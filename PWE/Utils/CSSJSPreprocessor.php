<?php

namespace PWE\Utils;


use DOMDocument;
use FilesystemIterator;
use PWE\Core\PWECMDJob;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CSSJSPreprocessor implements PWECMDJob
{

    public function __construct(PWECore $PWE)
    {

    }

    public function run()
    {
        $opts = getopt("j:c:r:p:d:");
        if (!$opts['p']) {
            throw new \InvalidArgumentException("-p option with root path required");
        }

        if (!$opts['d']) {
            throw new \InvalidArgumentException("-d option with destination path required");
        }

        $this->preprocess($opts['p'], $opts['d']);
    }

    public function preprocess($path, $dst)
    {
        PWELogger::info("Preprocessing " . $path . " to " . $dst);
        $dit = new RecursiveDirectoryIterator($path, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO);
        $rit = new RecursiveIteratorIterator($dit);
        /** @var $file \SplFileInfo */
        foreach ($rit as $file) {
            $filename = $file->getRealPath();
            if ($file->getExtension() != 'tpl' && $file->getExtension() != 'html') {
                //PWELogger::debug("Skip non-tpl: " . $filename);
                continue;
            }

            PWELogger::info("Preprocessing $filename");
            $this->preprocess_file_css($filename, $dst);
            $this->preprocess_file_js($filename, $dst);
        }
    }

    private function preprocess_file_gen($filename, $dst, $tagType, $ext)
    {
        $startTag = "<$tagType ";
        $endTag = "</$tagType>";
        $orig = file_get_contents($filename);
        $result = "";
        $pos = 0;
        $prevPos = 0;
        while (($pos = strpos($orig, $startTag, $pos)) !== false) {
            $pos_end = strpos($orig, $endTag, $pos);
            if ($pos_end !== FALSE) {
                PWELogger::debug("Res chunk: " . substr($orig, $prevPos, $pos - $prevPos));
                $result .= substr($orig, $prevPos, $pos - $prevPos);

                $cand = substr($orig, $pos, $pos_end - $pos + strlen($endTag));


                $marker = $this->getPreprocessMarker($cand, $tagType);
                if ($marker) {
                    PWELogger::info("Found block to write into: $marker$ext");
                    $dst_file = $dst . '/' . $marker . $ext;
                    $inner = $this->getInner($cand, $filename);

                    file_put_contents($dst_file,
                        "/* $filename */\n" . $inner . "\n\n",
                        FILE_APPEND);
                    $pos += strlen($cand);
                }
            }

            $prevPos = $pos;
            $pos++;
        }

        PWELogger::debug("Res end chunk: " . substr($orig, $prevPos, strlen($orig) - $prevPos));
        $result .= substr($orig, $prevPos, strlen($orig) - $prevPos);

        if ($result != $orig) {
            copy($filename, $filename . $ext . ".bak");
            PWELogger::debug("Result: $result");
            file_put_contents($filename, $result);
        }
    }

    private function preprocess_file_css($filename, $dst)
    {
        $this->preprocess_file_gen($filename, $dst, "style", '.css');
    }

    private function preprocess_file_js($filename, $dst)
    {
        $this->preprocess_file_gen($filename, $dst, "script", '.js');
    }

    private function getPreprocessMarker($cand, $tagType)
    {
        $doc = new DOMDocument();
        try {
            $doc->loadHTML($cand);
        } catch (\Exception $e) {
            //PWELogger::warn("Failed to parse tag from str: $cand", $e);
            return false;
        }

        $tags = $doc->getElementsByTagName($tagType);

        /** @var $item \DOMNode */
        foreach ($tags as $item) {
            /** @var $v \DOMAttr */
            foreach ($item->attributes as $v) {
                //PWELogger::debug("attr: " . $v->localName . "=>" . $v->value);
                if ($v->localName == "preprocessable") {
                    return $v->value;
                }
            }
        }
        return false;
    }

    private function getInner($cand, $filename)
    {
        $start = strpos($cand, '>') + 1;
        $end = strrpos($cand, "<");
        $contents = trim(substr($cand, $start, $end - $start));

        if (preg_match("/^{[^}]+}$/", $contents)) {
            // PWELogger::debug("Smarty value: $contents");
            if (preg_match("/\{([^}]+)\|readfile\}/", $contents, $matches1)) {
                //PWELogger::debug("Matches 1", $matches1);
                if (preg_match('/$smarty.current_dir|cat:[\'"](.+)[\'"]/', $matches1[1], $matches2)) {
                    //PWELogger::debug("Matches 2", $matches2);
                    $fname = $matches2[1];
                    return file_get_contents(dirname($filename) . $fname);
                } elseif (preg_match('/^[\'"](.+)[\'"]$/', $matches1[1], $matches2)) {
                    $fname = $matches2[1];
                    return file_get_contents($fname);
                } else {
                    throw new \RuntimeException("Failed to decide what to do with: $contents");
                }
            } else {
                throw new \RuntimeException("Failed to decide what to do with: $contents");
            }
        }

        return $contents;
    }
}