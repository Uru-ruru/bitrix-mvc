<?php

namespace Uru\BitrixBlade;

use Illuminate\View\FileViewFinder;

class ViewFinder extends FileViewFinder
{
    /**
     * Setter for paths.
     *
     * @param array $paths
     */
    public function setPaths($paths): void
    {
        $this->paths = $paths;
    }
}
