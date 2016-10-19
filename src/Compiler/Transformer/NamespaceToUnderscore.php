<?php

namespace RubtsovAV\RestDatabaseExporter\Compiler\Transformer;

class NamespaceToUnderscore implements TransformerInterface
{
    const CLASS_TOKENS = [
        T_CLASS,
        T_INTERFACE,
        T_EXTENDS,
        T_IMPLEMENTS,
        T_NEW,
    ];

    const CLASSNAME_TOKENS = [
        T_STRING,
        T_NS_SEPARATOR,
    ];

    private $namespace = '';
    private $classmap = [];

    /**
     * Converting class names with the namespace in the full names with an underscore.
     */
    public function transform($sourceCode)
    {
        $transformedCode = '';
        $tokens = token_get_all($sourceCode);

        $this->namespace = '';
        $this->classmap = [];

        if ($token = $this->normalizeToken($tokens[0])) {
            if ($token['type'] !== T_INLINE_HTML) {
                $transformedCode .= '<?php';
            }
        }

        while ($token = $this->getNextToken($tokens)) {
            if ($token['type'] === T_NAMESPACE) {
                $this->namespace = $this->parseNamespace($tokens);
            } elseif ($token['type'] === T_USE) {
                $this->classmap += $this->parseUse($tokens);
            } elseif (in_array($token['type'], static::CLASS_TOKENS)) {
                $transformedCode .= $token['data'];
                $token = $this->getNextToken($tokens);
                $transformedCode .= $token['data'];

                $classname = $this->parseClassname($tokens);
                $classname = $this->absoluteClassname($classname);
                $transformedCode .= $this->transormClassname($classname);
            } elseif ($token['type'] === T_FUNCTION) {
                $transformedCode .= $token['data'];
                $transformedCode .= $this->transformFunctionDeclaration($tokens);
            } elseif ($token['type'] === T_DOUBLE_COLON || $token['type'] === T_NS_SEPARATOR) {
                $prefixLength = 0;
                while ($token = $this->getPrevToken($tokens)) {
                    if (!in_array($token['type'], static::CLASSNAME_TOKENS)) {
                        break;
                    }
                    $prefixLength += strlen($token['data']);
                }

                if ($token['type'] != T_STATIC) {
                    $classname = $this->parseClassname($tokens);
                    if (!in_array(strtolower($classname), ['self', 'parent'])) {
                        if ($prefixLength > 0) {
                            $transformedCode = substr($transformedCode, 0, -$prefixLength);
                        }
                        $classname = $this->absoluteClassname($classname);
                        $transformedCode .= $this->transormClassname($classname);
                    }
                }

                $token = $this->getNextToken($tokens);
                $transformedCode .= $token['data'];
            } else {
                $transformedCode .= $token['data'];
            }
        }

        return $transformedCode;
    }

    private function getCurrentToken(&$tokens)
    {
        $token = current($tokens);
        if ($token === false) {
            return false;
        }
        return $this->normalizeToken($token);
    }

    private function getNextToken(&$tokens)
    {
        $token = next($tokens);
        if ($token === false) {
            return false;
        }
        return $this->normalizeToken($token);
    }

    private function getPrevToken(&$tokens)
    {
        $token = prev($tokens);
        if ($token === false) {
            return false;
        }
        return $this->normalizeToken($token);
    }

    private function normalizeToken($token)
    {
        $tokenType = is_array($token) ? $token[0] : null;
        $tokenData = (string) (is_array($token) ? $token[1] : $token);
        $tokenName = is_numeric($tokenType) ? token_name($tokenType) : null;
        return [
            'type' => $tokenType,
            'name' => $tokenName,
            'data' => $tokenData,
        ];
    }

    private function parseNamespace(&$tokens)
    {
        $namespace = '';
        while ($token = $this->getNextToken($tokens)) {
            if ($token['type'] === T_STRING || $token['type'] === T_NS_SEPARATOR) {
                $namespace .= $token['data'];
            } elseif ($token['data'] === ';') {
                break;
            }
        }
        return $namespace;
    }

    private function parseClassname(&$tokens)
    {
        $classname = '';
        while ($token = $this->getNextToken($tokens)) {
            if ($token['type'] === T_STRING || $token['type'] === T_NS_SEPARATOR) {
                $classname .= $token['data'];
            } else {
                break;
            }
        }
        $this->getPrevToken($tokens);
        return $classname;
    }

    private function parseUse(&$tokens)
    {
        $name = '';
        $alias = '';

        $isName = true;
        while ($token = $this->getNextToken($tokens)) {
            if ($token['type'] === T_STRING || $token['type'] === T_NS_SEPARATOR) {
                if ($isName) {
                    $name .= $token['data'];
                } else {
                    $alias .= $token['data'];
                }
            } elseif ($token['type'] === T_AS) {
                $isName = false;
            } elseif ($token['data'] === ';') {
                break;
            }
        }

        $name = ltrim($name, '\\');
        if (empty($alias)) {
            $alias = $this->shortenClassname($name);
        }
        return [$alias => $name];
    }

    private function transformFunctionDeclaration(&$tokens)
    {
        $transformedCode = '';
        while ($token = $this->getNextToken($tokens)) {
            $transformedCode .= $token['data'];
            if ($token['data'] === '(') {
                break;
            }
        }

        $isArgumentType = true;
        while ($token = $this->getNextToken($tokens)) {
            if ($token['data'] === ')') {
                $transformedCode .= $token['data'];
                break;
            } elseif ($token['type'] === T_VARIABLE) {
                $isArgumentType = false;
                $transformedCode .= $token['data'];
            } elseif ($token['data'] === ',') {
                $isArgumentType = true;
                $transformedCode .= $token['data'];
            } elseif ($isArgumentType == true
                && ($token['type'] === T_STRING || $token['type'] === T_NS_SEPARATOR)
            ) {
                $classname = $token['data'] . $this->parseClassname($tokens);
                $classname = $this->absoluteClassname($classname);
                $transformedCode .= $this->transormClassname($classname);
                $isArgumentType = false;
            } else {
                $transformedCode .= $token['data'];
            }
        }
        return $transformedCode;
    }

    private function shortenClassname($fullClassname)
    {
        $short = strrchr($fullClassname, '\\');
        if ($short === false) {
            return $fullClassname;
        }
        return substr($short, 1);
    }

    private function transormClassname($classname)
    {
        return ltrim(str_replace('\\', '_', $classname), '_');
    }

    private function absoluteClassname($classname)
    {
        if ($classname{0} == '\\') {
            return $classname;
        }

        if (isset($this->classmap[$classname])) {
            return $this->classmap[$classname];
        }

        return $this->namespace . '\\' . $classname;
    }
}
