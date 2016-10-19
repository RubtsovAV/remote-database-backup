<?php

namespace RubtsovAV\RestDatabaseExporter\Compiler\Transformer;

interface TransformerInterface
{
    /**
     *  Transform PHP code
     *
     * @param string $code
     *   PHP code which need to transform
     *
     * @return string
     *   Transormed PHP code
    */
    public function transform($phpCode);
}
