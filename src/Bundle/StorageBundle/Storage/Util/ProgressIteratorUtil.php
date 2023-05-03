<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Storage\Util;

use Iterator;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create a new iterator with map and run over the data with walk.
 *
 * @author Johnny Borg <johnny@e-active.nl>
 */
class ProgressIteratorUtil
{
    /**
     * @const string
     */
    public const FORMAT = '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%';

    /**
     * @var \Iterator
     */
    private $iterator;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(\Iterator $iterator, OutputInterface $output)
    {
        $this->iterator = $iterator;
        $this->output = $output;
    }

    /**
     * @return $this
     */
    public function map(\Closure $closure)
    {
        if (iterator_count($this->iterator)) {
            $progress = $this->createProgress();
            $iterator = new \ArrayIterator();

            foreach ($this->iterator as $item) {
                $result = $closure($item);
                if ($result) {
                    $iterator[] = $result;
                }

                $progress->advance();
            }

            // Map creates a new collection
            $this->iterator = $iterator;
            $progress->finish();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function walk(\Closure $closure)
    {
        if (iterator_count($this->iterator)) {
            $progress = $this->createProgress();

            foreach ($this->iterator as $item) {
                $closure($item);

                $progress->advance();
            }

            $progress->finish();
        }

        return $this;
    }

    /**
     * @return ProgressBar
     */
    protected function createProgress()
    {
        $count = iterator_count($this->iterator);

        $progress = new ProgressBar($this->output, $count);
        $progress->start();
        $progress->setFormat(self::FORMAT);
        $progress->setRedrawFrequency(ceil($count / 50));

        return $progress;
    }
}
