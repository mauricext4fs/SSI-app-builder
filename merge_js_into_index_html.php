<?php

class Merge {
    
    public function getIndexFile($strFile = "index.html", $strWorkingDir)
    {
        $strPath = $strWorkingDir . "/";
        $strPath = str_replace(" ", "\ ", $strPath);
        $strContent = file_get_contents($strPath . $strFile);

        return $strContent;
    }

    public function parseFile($strFile = "index.html", $strWorkingDir)
    {
        $strContent = "";
        $arrMatches = [];
        $strFile = preg_replace("/(\?[a-z0-9\=]*)$/i", "", $strFile);
        $strFilePath = sprintf("%s/%s", $strWorkingDir, $strFile);
        printf("%s\n", $strFilePath);
        $arrResult = [];
        /*
         * If the file is a javascript file... we do not recurse... we process it directly.
         * As of today 2014-10-4 it is not possible that a javascript file would include 
         * another javascript file anyway.
         */
        if (strstr($strFile, ".js")) {
            exec("echo ''> vlad.js");
            $strCmd = sprintf("java -jar %s/yuicompressor-2.4.8.jar %s -o vlad.js", __DIR__, str_replace(" ", "\ ", $strFilePath));
            printf("Command to yuicompressor: %s", $strCmd);
            exec($strCmd, $arrResult, $numReturn);
            if ($numReturn) {
                printf("File %s not processed with yuicompressor with return value of %s", $strFile, print_r($arrResult, true));
                // Including the file without compression... compression fail on that file
                $strContent = file_get_contents($strFilePath);
            } else {
                printf("File %s processed with yuicompressor with return value of %s", $strFile, $numReturn);
                $strContent = file_get_contents("vlad.js");
            }
        } else {
            $fs = fopen($strFilePath, "r"); 
            if (!is_resource($fs)) {
                throw new Exception(sprintf("File: %s could not be open", $strFilePath));
            }
            while (!feof($fs)) {
                $strBuf = fgets($fs);
                if (strstr($strBuf, "\"js/") && strstr($strBuf, "<script")) {
                    // Found a file to include
                    // Remove all space
                    $strBuf = trim($strBuf);
                    $numMatches = preg_match("/\<script src\=\"(.*)\"\>\<\/script\>/", $strBuf, $arrMatches);
                    if ($numMatches !== FALSE && $numMatches !== 0) {
                        $strContent .= sprintf("<script type=\"text/javascript\">%s</script>\n", $this->parseFile($arrMatches[1], $strWorkingDir)); 
                    } else {
                        $strContent .= $strBuf;
                    }
                } else {
                    $strContent .= $strBuf;
                }

            }
            fclose($fs);
        }
        return $strContent;
    }

    public function putContentInIndexFile($strContent, $strWorkingDir)
    {
        file_put_contents($strWorkingDir . "/index.html", $strContent); 
    }

}

$m = new Merge();
//$strIndexContent = $m->getIndexFile();
$strMergedContent = $m->parseFile("index.html", $strWorkingDir = getcwd());
$m->putContentInIndexFile($strMergedContent, $strWorkingDir);
