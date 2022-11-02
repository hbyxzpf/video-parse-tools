<?php

namespace Smalls\VideoTools\Logic;

use Smalls\VideoTools\Enumerates\UserGentType;
use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Utils\CommonUtil;

/**
 * 努力努力再努力！！！！！
 * Author：smalls
 * Github：https://github.com/smalls0098
 * Email：smalls0098@gmail.com
 * Date：2020/8/5 - 16:21
 **/
class H5KuaiShouLogic extends Base
{

    private $contents;


    public function setContents()
    {
        $longUrl = $this->redirects($this->url);
        $queryStr = parse_url($longUrl,PHP_URL_QUERY);
        parse_str($queryStr,$queryArr);
        $data = collect($queryArr)->only(["fid","shareToken","shareObjectId","shareMethod","shareId","shareResourceType","shareChannel","kpn","subBiz","env","photoId"])->toArray();
        $data["isLongVideo"] = false;
        $data["h5Domain"] = "v.m.chenzhongtech.com";
        $resp = $this->post("https://m.gifshow.com/rest/wd/photo/info?kpn=KUAISHOU&captchaToken=",json_encode($data),[
            "Content-Type"=>"application/json",
            "user-agent"=>"Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Mobile Safari/537.36",
            "Cookie"=>"_did=web_518883321649BCF8; did=web_94a05b1178c34af3b97bc4d644d8c963",
            "Referer"=>$longUrl
        ]);
        $this->contents = $resp;
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
    public function getUrl()
    {
        return $this->url;
    }


    public function getVideoUrl()
    {
        return isset($this->contents["photo"]["mainMvUrls"])?$this->contents["photo"]["mainMvUrls"][0]["url"]:"";
    }

    public function getVideoImage()
    {
        return isset($this->contents["photo"]["coverUrls"])?$this->contents["photo"]["coverUrls"][0]["url"]:"";
    }

    public function getImageList()
    {
        $return = [];
        if (isset($this->contents["atlas"]["list"])){
            foreach ($this->contents["atlas"]["list"] as $link){
                $return[] = sprintf("https://%s%s",$this->contents["atlas"]["cdn"][0],$link);
            }
        }
        return $return;
    }

    public function getType(){
        return $this->getVideoUrl()?"video":"image";
    }
    public function getVideoDesc()
    {
        return $this->contents["photo"]["caption"]??"";
    }

    public function getUsername()
    {
        return isset($this->contents['user']['avatar']) ? $this->contents['user']['avatar'] : '';

    }

    public function getUserPic()
    {
        return isset($this->contents['user']['name']) ? $this->contents['user']['name'] : '';

    }

}
