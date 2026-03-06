<?php

namespace Integrated\Bundle\UserBundle\Tests\Form\Type;

use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

class DeleteFormTypeTest extends TestCase
{
    public function testDeleteFormUsesDeleteHttpMethod(): void
    {
        $factory = Forms::createFormFactoryBuilder()
            ->addType(new DeleteFormType())
            ->getFormFactory();

        $form = $factory->create(DeleteFormType::class);

        self::assertSame('DELETE', $form->getConfig()->getMethod());
    }
}
