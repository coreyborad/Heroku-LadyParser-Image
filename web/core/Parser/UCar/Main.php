<?php
namespace Core\Parser\UCar;

use PDO;
use phpQuery;

class Main
{
    public function __construct()
    {
        $this->url                = "https://gallery.u-car.com.tw/galleries?page=1";
        $this->domain             = "https://gallery.u-car.com.tw/";
        $this->image_url          = "https://image.u-car.com.tw/";
        $this->page_gallery_array = [];
        $this->images             = [];
        // 連線PostgreSQL
        $db = parse_url(getenv("DATABASE_URL"));
        $pdo = new PDO("pgsql:" . sprintf(
            "host=%s;port=%s;user=%s;password=%s;dbname=%s",
            $db["host"],
            $db["port"],
            $db["user"],
            $db["pass"],
            ltrim($db["path"], "/")
        ));
        $this->pdo = $pdo;
    }
    public function _Start()
    {
        $gallery_page = $this->_GetContent($this->url);
        $this->_ParserGalleryNumber($gallery_page);
        foreach ($this->page_gallery_array as $value) {
            $gallery = $this->_GetContent($this->domain . $value['url']);
            $this->_ParserGallery($gallery, $value['url'], $value['title'], $value['id']);
        }
        $this->_InsertToDB();
    }
    private function _GetContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36");
        $html_content = curl_exec($ch);
        curl_close($ch);
        return $html_content;
    }
    private function _ParserGalleryNumber($content)
    {
        $pq_obj = phpQuery::newDocument($content);
        foreach ($pq_obj[".list_inner_ga a"] as $ahref) {
            array_push($this->page_gallery_array, [
                "url"   => trim(pq($ahref)->attr('href')),
                "title" => trim(pq($ahref)->find("p")->html()),
                "id"    => explode("/", trim(pq($ahref)->attr('href')))[2],
            ]);
        }
        return true;
    }
    private function _ParserGallery($content, $url, $title, $id)
    {
        $pq_obj = phpQuery::newDocument($content);
        $index  = 0;
        foreach ($pq_obj[".gallery_content a"] as $ahref) {
            if ($index > 5) {
                break;
            }
            array_push($this->images, [
                "image" => $this->image_url . $id . "/photo_" . trim(pq($ahref)->attr('data-id')) . ".jpg",
                "url"   => $this->domain . "gallery/" . $id,
                "title" => $title,
            ]);
            $index++;
        }
    }
    private function _InsertToDB()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS cars (
            id INT NOT NULL,
            img_url VARCHAR(300) NULL,
            title VARCHAR(300) NULL,
            gallery_url VARCHAR(300) NULL,
            PRIMARY KEY (id));
          ");
        $this->pdo->exec("TRUNCATE cars;");
        $sql  = 'INSERT INTO cars(id, img_url, title, gallery_url) VALUES';
        foreach ($this->images as $key => $value) {
            $sql = $sql . "(".$key.",'".$value["image"]."','".$value["title"]."','".$value["url"]."'),";
        }
        $sql = substr($sql,0,-1);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    }
}
