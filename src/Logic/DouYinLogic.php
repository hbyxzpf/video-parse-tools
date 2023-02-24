<?php
declare (strict_types=1);

namespace Smalls\VideoTools\Logic;

use Smalls\VideoTools\Enumerates\UserGentType;
use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Utils\CommonUtil;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/6/10 - 13:05
 **/
class DouYinLogic extends Base
{

    private $contents;
    private $itemId;

    public function setItemIds()
    {
        if (strpos($this->url, '/share/video')) {
            $url = $this->url;
        } else {
            $url = $this->redirects($this->url, [], [
                'User-Agent' => UserGentType::ANDROID_USER_AGENT,
            ]);
        }
        preg_match('/video\/([0-9]+)\//i', $url, $matches);
        if (CommonUtil::checkEmptyMatch($matches)) {
            throw new ErrorVideoException("item_id获取不到");
        }
        $this->itemId = $matches[1];
    }

    public function setContents()
    {
        $contents = $this->get('https://www.iesdouyin.com/aweme/v1/web/aweme/detail/', [
            'aweme_id' => $this->itemId,
            'aid'=>1128,
            "version_name"=>"23.5.0",
            "device_platform"=>"android",
            "os_version"=>"2333"
        ], [
            // @Todo 分析此接口header校验规则，完善参数
            'User-Agent' => UserGentType::ANDROID_USER_AGENT, // user-agent请求中必须，否则返回状态码444。常规UA无有效数据返回，可能存在某种校验，临时使用postmanUA头，保证正常返回
            'Referer'    => "https://www.iesdouyin.com",
            'Host'       => "www.iesdouyin.com",
        ]);
        if ((isset($contents['status_code']) && $contents['status_code'] != 0) || empty($contents['aweme_detail']['video']['play_addr']['uri'])) {
            throw new ErrorVideoException("parsing failed");
        }
        $this->contents = $contents['aweme_detail'];
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getVideoUrl()
    {
        if (empty($this->contents['video']['play_addr']['uri'])) {
            return '';
        }
        return "https://aweme.snssdk.com/aweme/v1/play/?video_id=".$this->contents['video']['play_addr']['uri']."&ratio=1080p&line=0";
    }

    public function getVideoImage()
    {
        return CommonUtil::getData($this->contents["video"]['cover']['url_list'][0]);
    }

    public function getVideoDesc()
    {
        return CommonUtil::getData($this->contents['desc']);
    }

    public function getUsername()
    {
        return CommonUtil::getData($this->contents['author']['nickname']);
    }

    public function getUserPic()
    {
        return CommonUtil::getData($this->contents['author']['avatar_thumb']['url_list'][0]);
    }

    public function getImageList(){
        $images = [];
        if (!empty($this->contents["images"])){
            foreach ($this->contents["images"] as $image){
                $images[] =  $image["url_list"][3];
            }
        }
        return $images;
    }

    public function getType(){
        return (CommonUtil::getData($this->contents['aweme_type']) == 2)?"image":"video";
    }
}
