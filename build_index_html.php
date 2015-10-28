<?php

class Build {
    
    public function parseFile($strFile = "index.shtml")
    {
        $strContent = "";
        $arrMatches = [];
        $i = 0;
        $strPath = __DIR__ . "/../";
        $fs = fopen($strPath.$strFile, "r"); 
        while (!feof($fs)) {
            $strBuf = fgets($fs);
            if (strstr($strBuf, ".shtml") || strstr($strBuf, ".html")) {
                // Found a file to include
                // Remove all space
                $strBuf = trim($strBuf);
                $numMatches = preg_match("/\<\!--\#include file\=\"(.*)\" --\>/", $strBuf, $arrMatches);
                if ($numMatches !== FALSE && $numMatches !== 0) {
                    $strContent .= $this->parseFile($arrMatches[1]); 
                } else {
                    $strContent .= $strBuf;
                }
            } else {
                $strContent .= $strBuf;
            }

        }
        fclose($fs);
        return $strContent;
    }

    public function putContentInIndexFile($strContent)
    {
        file_put_contents(__DIR__ . "/../index.html", $strContent); 
    }

}

$b = new Build();
$strIndexContent = $b->parseFile("index.shtml");
$b->putContentInIndexFile($strIndexContent);
