<?php

namespace RubtsovAV\RemoteDatabaseBackup\Compiler;

use RubtsovAV\RemoteDatabaseBackup\Compiler\Transformer\TransformerInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Compiler
{
    private $transformers = [];
    private $files = [];

    public function addTransformer(TransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;
    }

    public function addDir($dirname, $excludePaths = [])
    {
        $dirname = realpath($dirname);
        if (!$dirname) {
            return false;
        }

        if (!empty($excludePaths)) {
            $excludePaths = array_map('realpath', $excludePaths);
            $excludePaths = array_filter($excludePaths);
        }

        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        foreach ($iter as $path => $item) {
            if ($item->isFile() 
                && $item->getExtension() == 'php' 
                && !in_array($path, $excludePaths)
            ){
                $this->files[] = $path;
            }
        }
    }

    public function addFile($file)
    {
        $this->files[] = realpath($file);
    }

    public function compile()
    {
        $result = "<?php\n";
        foreach ($this->files as $file) {
            $fileContent = file_get_contents($file);
            foreach ($this->transformers as $transformer) {
                $fileContent = $transformer->transform($fileContent);
            }
            $fileContent = trim($fileContent);
            $fileContent = ltrim($fileContent, '<?php');
            $fileContent = rtrim($fileContent, '?>');
            $fileContent = trim($fileContent);
            $result .= $fileContent . "\n";
        }
        return $result;
    }
}
