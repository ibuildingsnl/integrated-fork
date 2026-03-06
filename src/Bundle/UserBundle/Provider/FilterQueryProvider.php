<?php

namespace Integrated\Bundle\UserBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;

class FilterQueryProvider
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param array|null $data
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsers($data)
    {
        $queryBuilder = $this->userManager->createQueryBuilder()->select('User');

        if (isset($data['groups'])) {
            $queryBuilder
                ->leftJoin('User.groups', 'Groups')
                ->where('Groups IN (:groups)')
                ->setParameter('groups', array_filter($data['groups']));
        }

        if (isset($data['scope'])) {
            $queryBuilder
                ->leftJoin('User.scope', 'Scope')
                ->andWhere('Scope IN (:scope)')
                ->setParameter('scope', array_filter($data['scope']));
        }

        if (isset($data['q'])) {
            $queryBuilder
                ->andWhere('User.username LIKE :q OR User.email LIKE :q')
                ->setParameter('q', '%'.$data['q'].'%');
        }

        if (isset($data['has_relation']) && \is_array($data['has_relation'])) {
            $selected = array_values(array_filter($data['has_relation'], static fn ($value) => $value === '0' || $value === '1'));

            if (\count($selected) === 1) {
                if ($selected[0] === '1') {
                    $queryBuilder
                        ->andWhere('User.relation IS NOT NULL')
                        ->andWhere("User.relation <> ''");
                } else {
                    $queryBuilder->andWhere("(User.relation IS NULL OR User.relation = '')");
                }
            }
        }

        return $queryBuilder->getQuery();
    }

    public function getGroupChoices($data)
    {
        $sql = 'SELECT s.id, s.name, count(g.group_id) as count
            FROM security_groups s
            INNER JOIN security_user_groups g ON s.id = g.group_id
            INNER JOIN security_users u ON g.user_id = u.id
            WHERE (:scope <= 0 OR u.scope = :scope)
            AND (:groups <= 0 OR s.id IN (:groups))
            GROUP BY g.group_id HAVING count > 0
        ';

        /** @var EntityManagerInterface $manager */
        $manager = $this->userManager->getObjectManager();

        return $this->formatChoices($manager->createNativeQuery($sql, $this->getMapping()), $data);
    }

    public function getScopeChoices($data)
    {
        $sql = 'SELECT s.id, s.name, count(u.scope) as count
            FROM security_scopes s
            INNER JOIN security_users u ON s.id = u.scope
            INNER JOIN security_user_groups g ON u.id = g.user_id
            WHERE (:scope <= 0 OR u.scope = :scope)
            AND (:groups <= 0 OR g.group_id IN (:groups))
            GROUP BY u.scope
        ';

        /** @var EntityManagerInterface $manager */
        $manager = $this->userManager->getObjectManager();

        return $this->formatChoices($manager->createNativeQuery($sql, $this->getMapping()), $data);
    }

    private function getMapping()
    {
        $mapping = new ResultSetMapping();
        $mapping->addScalarResult('id', 'id');
        $mapping->addScalarResult('count', 'count');
        $mapping->addScalarResult('name', 'name');

        return $mapping;
    }

    private function formatChoices($query, $data)
    {
        $query->setParameter('scope', (isset($data['scope'])) ? $data['scope'] : 0);
        $query->setParameter('groups', (isset($data['groups'])) ? array_filter($data['groups']) : 0);

        $choices = [];
        foreach ($query->getResult() as $result) {
            $choices[\sprintf('%s %d', $result['name'], $result['count'])] = $result['id'];
        }

        return $choices;
    }
}
