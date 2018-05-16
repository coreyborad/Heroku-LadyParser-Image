<?php
namespace Core\Parser\Ptt;

use phpQuery;

class Main
{
    public function __construct(){
        $this->conf = [
            "page_count" => 15,
            "nrec"       => 30
        ];
        $this->PTT_url    = "https://www.ptt.cc";
        $this->Beauty_url = "/bbs/Beauty/index.html";
    }
    public function _Start(){
        //Pages url
        $total_pages_url = [];
        $total_image_url = [];
        //******Start Parser******
        $page_count = 0;
        $Beauty_url = $this->Beauty_url;
        while ($page_count < $this->conf["page_count"]) {
            //Get html content
            $content = $this->_GetContent($this->PTT_url . $Beauty_url);
            //Parser list
            $parser_list_result = $this->_ParserList($content, $page_count);
            foreach ($parser_list_result["lists_href"] as $value) {
                array_push($total_pages_url, $value);
            }
            $page_count = $parser_list_result["page_count"];
            $Beauty_url = $parser_list_result["last_href"];
            echo $page_count."\n";
        }
        foreach ($total_pages_url as $page_url) {
            //Get html content
            $content            = $this->_GetContent($this->PTT_url . $page_url);
            $parser_page_result = $this->_ParserPage($content);
            foreach ($parser_page_result as $value) {
                array_push($total_image_url, $value);
            }
            echo count($total_image_url)."\n";
        }
        return $total_image_url;
    }
    private function _GetContent($url){
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36");
        $html_content = curl_exec($ch);
        curl_close($ch);
        return $html_content;
    }
    private function _ParserList($content, $page_count)
    {
        $pq_obj     = phpQuery::newDocument($content);
        $lists_href = [];
        $last_href  = "";
        //記錄上一頁的網址
        foreach ($pq_obj["#action-bar-container div.btn-group-paging a"] as $ahref) {
            $val = trim(pq($ahref)->html());
            if ($val === "‹ 上頁") {
                $last_href = trim(pq($ahref)->attr('href'));
                break;
            }
        }
        //開始對文章清單做篩選
        foreach ($pq_obj[".r-list-container .r-ent"] as $list) {
            //檢查是否已經收集到10篇文章
            if ($page_count >= $this->conf["page_count"]) {
                break;
            }
            //只針對一般文章做擷取
            $mark = trim(pq($list)->find("div.mark")->html());
            if ($mark !== "") {
                continue;
            }
            $title = trim(pq($list)->find("div.title a")->html());
            //過濾掉空白tile
            if ($title === "") {
                continue;
            }
            //只保留[正妹]
            if (strpos($title, "[正妹]") === false) {
                continue;
            }
            //推噓文處理
            $nrec = trim(pq($list)->find("div.nrec span")->html());
            //噓文會是"X2"(檢查是否為字串)
            if (!is_numeric($nrec)) {
                $nrec = 0;
            }
            //爆文要另外處理
            if ($nrec === "爆") {
                $nrec = 100;
            }
            //只針對10推以上的
            $nrec = (int) $nrec;
            if ($nrec < $this->conf["nrec"]) {
                continue;
            }
            array_push($lists_href, trim(pq($list)->find("div.title a")->attr('href')));
            $page_count++;
        }
        return [
            "lists_href" => $lists_href,
            "page_count" => $page_count,
            "last_href"  => $last_href,
        ];
    }
    private function _ParserPage($content)
    {
        $pq_obj     = phpQuery::newDocument($content);
        $images_url = [];
        $id_list    = [];
        foreach ($pq_obj["#main-content div.richcontent"] as $image_href) {
            $id = trim(pq($image_href)->find("blockquote")->attr("data-id"));
            array_push($id_list, $id);
        }
        foreach ($pq_obj["#main-content a"] as $value) {
            $href = pq($value)->attr("href");
            if (false !== strpos($href, "https://i.imgur.com")) {
                foreach ($id_list as $id) {
                    if (false !== strpos($href, $id.".")) {
                        array_push($images_url, $href);
                        break;
                    }
                }
            }
        }
        return $images_url;
    }
}
