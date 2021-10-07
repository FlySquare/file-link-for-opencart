<?php
$openCartPath = 'C:/Users/umutk/Desktop/epazarsoft/iyi/core/';

if (isset($argv) && count($argv)>1) {
    if ($argv[1] == "remove" && file_exists('_links-created.json')){
        $old_paths = json_decode(file_get_contents('_links-created.json'),true);
        foreach($old_paths as $path){
            unlink($path);
        }
        unlink('_links-created.json');
        echo "\nTüm Linkler Kaldırıldı\n";
        exit();
    }else{
        echo "\nHatalı Giriş\n";
        exit();
    }
}
function getFiles($dir, &$results = array())
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getFiles($path, $results);
            //$results[] = $path;
        }
    }

    return $results;
}

$links = [];
$linksDirs = [];

$exclude = [
    __DIR__ . DIRECTORY_SEPARATOR . '.git',
    __DIR__ . DIRECTORY_SEPARATOR . '.idea',
    '.gitignore',
    'build.sh',
    __DIR__ . DIRECTORY_SEPARATOR . 'oclink.php'
];

foreach (getFiles('.') as $file) {
    $isModuleFile = true;

    foreach ($exclude as $excludeFile) {
        if (strpos($file, $excludeFile) !== false) {
            $isModuleFile = false;
        }
    }

    if ($isModuleFile) {
        if (strpos($file, '.xml') !== false) {
            $link = str_replace(__DIR__ . DIRECTORY_SEPARATOR, $openCartPath.'upload/system/', $file);
        } else {
            $link = str_replace(__DIR__ . DIRECTORY_SEPARATOR, $openCartPath, $file);
        }
        $linkDir = dirname($link);
        if (!in_array($linkDir, $linksDirs)) {
            $linksDirs[] = $linkDir;
        }
        $links[] = [
            'file' => $file,
            'link' => $link
        ];
    }
}

foreach ($linksDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

if (file_exists('_links-created.json')) {
    $previouslyCreatedLinksJson = file_get_contents('_links-created.json');
    if ($previouslyCreatedLinksJson !== false) {
        $previouslyCreatedLinks = json_decode($previouslyCreatedLinksJson, true);
        foreach ($previouslyCreatedLinks as $previouslyCreatedLink) {
            if (file_exists($previouslyCreatedLink)) {
                unlink($previouslyCreatedLink);
            }
        }
    }
    unlink('_links-created.json');
}

$createdLinks = [];

foreach ($links as $key => $link) {
    if (file_exists($link['link'])) {
        unlink($link['link']);
    }
    $linkCreated = symlink($link['file'], $link['link']);
    if ($linkCreated) {
        $createdLinks[] = $link['link'];
    }
}

file_put_contents('_links-created.json', json_encode($createdLinks));
