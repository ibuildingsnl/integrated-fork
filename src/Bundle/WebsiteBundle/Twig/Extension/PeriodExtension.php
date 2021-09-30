<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_Environment;
use DateTime;
use DateTimeInterface;
/**
 * @author Koen Prins <koen@e-active.nl>
 */
class PeriodExtension extends Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'integrated_period_formatter',
                function (Twig_Environment $twig, DateTime $startDate, DateTime $endDate) : string {
                    return $this->periodFilter($twig, $startDate, $endDate);
                },
                ['needs_environment' => true]
            ),
        ];
    }

    /**
     * @param Twig_Environment $twig
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return string
     */
    public function periodFilter(Twig_Environment $twig, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $filter = $twig->getFilter('localizeddate');

        $period = \call_user_func($filter->getCallable(), $twig, $startDate, 'long', 'short');

        if ($endDate) {
            $period .= ' - ';
            $dateFormat = ($startDate->format('Ymd') === $endDate->format('Ymd') ? 'none' : 'long');
            $period .= \call_user_func($filter->getCallable(), $twig, $endDate, $dateFormat, 'short');
        }

        return $period;
    }
}
