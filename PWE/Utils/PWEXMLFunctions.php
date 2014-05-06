<?php

namespace PWE\Utils;

use PWE\Core\PWELogger;

/**
 * Description of PWEXMLFunctions
 *
 * @author undera
 */
abstract class PWEXMLFunctions
{

    /**
     * функция находит в массиве узлов атрибут с именем
     * $name и значением этого атрибута $val и возвращает его индекс
     * @param $nodes array массив узлов для поиска
     * @param $name string имя атрибута
     * $param $val string значение атрибута
     * @return integer Индекс найденного узла. Eсли ничего не найдено - возвращаем -1
     */
    public static function findNodeWithAttributeValue(&$nodes, $name, $val)
    {
        foreach ($nodes ? $nodes : array() as $k => $node) {
            if ($node['!a'][$name] == $val) {
                return $k;
            }
        }

        PWELogger::debug("%s=%s not found in xml data", $name, $val);
        return -1;
    }

    public static function cleanEmptyNodes(&$node)
    {
        foreach ($node['!c'] ? $node['!c'] : array() as $k => $v) {
            foreach ($node['!c'][$k] ? $node['!c'][$k] : array() as $kk => $vv) {
                self::cleanEmptyNodes($node['!c'][$k][$kk]);
                // if only !p left - drop the node
                if (!sizeof($node['!c'][$k][$kk]) ||
                    (sizeof($node['!c'][$k][$kk]) == 1 && $node['!c'][$k][$kk]['!p'])
                ) {
                    unset($node['!c'][$k][$kk]);
                }
            }

            if (!sizeof($node['!c'][$k])) {
                unset($node['!c'][$k]);
            }
        }

        if (!sizeof($node['!c'])) {
            unset($node['!c']);
        }

        if (!sizeof($node['!a'])) {
            unset($node['!a']);
        }

        if (!strlen($node['!v'])) {
            unset($node['!v']);
        }
    }

}

?>