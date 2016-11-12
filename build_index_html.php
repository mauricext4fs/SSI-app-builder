<?php

class Build {
    
    public function parseFile($strFile = "index.shtml", $strWorkingDir)
    {
        $strContent = "";
        $arrMatches = [];
        $i = 0;
        $strPath = $strWorkingDir . "/";
        $fs = fopen($strPath.$strFile, "r"); 
        if (!$fs) {
            throw new Exception (sprintf("Cannot process file: %s", $strPath.$strFile));
        }
        printf("Processing File: %s\n", $strPath.$strFile);
        while (!feof($fs)) {
            $strBuf = fgets($fs);
            if (strstr($strBuf, ".shtml") || strstr($strBuf, ".html")) {
                // Found a file to include
                // Remove all space
                $strBuf = trim($strBuf);
                $numMatches = preg_match("/\<\!--\#include file\=\"(.*)\" --\>/", $strBuf, $arrMatches);
                if ($numMatches !== FALSE && $numMatches !== 0) {
                    $strContent .= $this->parseFile($arrMatches[1], getcwd()); 
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

    public function putContentInIndexFile($strContent, $strWorkingDir)
    {
        file_put_contents($strWorkingDir . "/index.html", $strContent); 
    }

}

$b = new Build();
$strIndexContent = $b->parseFile("index.shtml", $strWorkingDir = getcwd());
$b->putContentInIndexFile($strIndexContent, $strWorkingDir);
