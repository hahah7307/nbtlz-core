<?php

namespace app\Manage\model;

use OSS\Core\OssException;
use OSS\Credentials\Credentials;
use OSS\Credentials\CredentialsProvider;
use OSS\Credentials\StaticCredentialsProvider;
use OSS\Http\RequestCore_Exception;
use OSS\OssClient;
use think\Config;
use think\Model;

class AlibabaCloudCredentialsWrapper extends Model implements CredentialsProvider
{
    /**
     * @var Credentials
     */
    private $wrapper;

    public function __construct($wrapper){
        $this->wrapper = $wrapper;

        parent::__construct();
    }
    public function getCredentials(): StaticCredentialsProvider
    {
        $ak = $this->wrapper->getAccessKeyId();
        $sk = $this->wrapper->getAccessKeySecret();
        $token = $this->wrapper->getSecurityToken();

        return new StaticCredentialsProvider($ak, $sk, $token);
    }

    static public function uploadFile($url, $targetPath): array
    {
        // 加载配置文件
        Config::load(APP_PATH . 'web.php');

        $accessKeyId = Config::get('OSS_ACCESS_KEY_ID');
        $accessKeySecret = Config::get('OSS_ACCESS_KEY_SECRET');
        $endpoint = Config::get('OSS_ENDPOINT');
        $bucket = Config::get('OSS_BUCKET');
        $options = array(
            OssClient::OSS_CONTENT_TYPE => 'image/jpg',
        );

        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $uploadRes = $ossClient->uploadFile($bucket, $targetPath, $url, $options);
            if (!empty($uploadRes['oss-request-url'])) {
                return ['code' => 1, 'url' => $uploadRes['oss-request-url']];
            } else {
                return ['code' => 0, 'msg' => '上传失败！'];
            }
        } catch(OssException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        } catch (RequestCore_Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    static public function signUrl($url): array
    {
        // 加载配置文件
        Config::load(APP_PATH . 'web.php');

        $accessKeyId = Config::get('OSS_ACCESS_KEY_ID');
        $accessKeySecret = Config::get('OSS_ACCESS_KEY_SECRET');
        $endpoint = Config::get('OSS_ENDPOINT');
        $bucket = Config::get('OSS_BUCKET');
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $timeout = 3600;
            $options = array(
//                     OssClient::OSS_FILE_DOWNLOAD => $download_file,
//                     OssClient::OSS_PROCESS => "image/resize,m_fixed,h_100,w_100",
            );
            $signedUrl = $ossClient->signUrl($bucket, $url, $timeout, "GET", $options);
            if ($signedUrl) {
                return ['code' => 1, 'url' => $signedUrl, 'expire' => date('Y-m-d H:i:s', time() + 3600)];
            } else {
                return ['code' => 0, 'msg' => '生成失败！'];
            }
        } catch (OssException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }
}
