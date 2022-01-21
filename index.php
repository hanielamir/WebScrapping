<?php
include 'simple_html_dom/simple_html_dom.php';
ini_set('max_execution_time', 0);
$servername = "localhost";
$username = "root";
$password = "";
$database = "lab";
// Create a connection
$conn = mysqli_connect($servername, $username, $password, $database);
$target_site='https://torrentfreak.com';
$content=file_get_html($target_site);
$categoriesHrefs=[];
foreach ($content->find('.menu .menu__list .menu__item') as $item)
{
    foreach ($content->find('a') as $a) {
        $href=$a->attr['href'];
        if (str_contains($href,'/category')) {
            $categoriesHrefs[] = $a->attr['href'];
        }
    }
}

$categoriesHrefs=array_unique($categoriesHrefs);
$categoriesArticlesHrefs=[];
if (!empty($categoriesHrefs)) {
    $inc = 0;
    foreach ($categoriesHrefs as $categoriesHref) {
        do {
            $categoriesHrefNew = $categoriesHref . 'page/' . $inc;
            $urlStats = get_headers($categoriesHrefNew);
            $string = $urlStats[0];
            $contentCategory = file_get_html($categoriesHrefNew);
            foreach ($contentCategory->find('article') as $item) {
                foreach ($item->find('a') as $a) {
                    $articleHref = $a->attr['href'];
                    $urlArticleStats = get_headers($articleHref);
                    $stringArticle = $urlArticleStats[0];
                    if (!strpos($string, "404")) {
                        $contentArticle = file_get_html($articleHref);
                        if ($contentArticle) {
                            $title = $contentArticle->find('.hero__title', 0);
                            $titleText = $title->plaintext;
                            $body = $contentArticle->find('.article', 0);
                            $bodyText = $body->plaintext;
                            $author = $contentArticle->find('.hero__published', 0);
                            $authorText = $author->plaintext;
                            $authorTextArr = explode('by',$authorText);
                            $authorName=$authorTextArr[1];
                            $sql = "INSERT INTO `articles` ( `title`,`body`,`author`) VALUES ('$titleText','$bodyText','$authorName')";
                            $result = mysqli_query($conn, $sql);
                        }
                    }
                }
            }
            $inc++;
        }
        while(!strpos($string, "404"));
    }
}
print_r('Done Scraping TorrentFreak Site');
die();
?>