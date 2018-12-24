<?php

class YandexDiskWebdav
{

    const WEBDAV_URL = 'https://webdav.yandex.com.tr/';
    public static $credentials = [];
    public static $posts = '';

    /**
     * @param $username
     * @param $password
     */
    public static function setCredentials($username, $password)
    {
        self::$credentials = [$username, $password];
    }

    /**
     * @param $request
     * @param $url
     * @param array $headers
     * @param null $file
     * @return mixed
     */
    public static function request($request, $url, $headers = [], $file = null)
    {
        $ch = curl_init(self::WEBDAV_URL . $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, implode(':', self::$credentials));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, self::$posts);
        if ($file) curl_setopt($ch, CURLOPT_INFILE, $file);
        $result = curl_exec($ch);
        curl_close($ch);
        self::$posts = ''; // clear
        return $result;
    }

    /**
     * @param $folder
     * @return mixed
     */
    public static function createFolder($folder)
    {
        return self::request('MKCOL', $folder);
    }

    /**
     * @param string $folder
     * @return array
     */
    public static function listFolder($folder = '')
    {
        $xml = self::request('PROPFIND', $folder, [
            'Depth: 1'
        ]);
        preg_match_all('@<d:response><d:href>(.*?)</d:href><d:propstat><d:status>(.*?)</d:status><d:prop><d:creationdate>(.*?)</d:creationdate><d:displayname>(.*?)</d:displayname><d:getlastmodified>(.*?)</d:getlastmodified><d:resourcetype><d:collection/></d:resourcetype></d:prop></d:propstat></d:response>@', $xml, $folders);
        preg_match_all('@<d:response><d:href>(.*?)</d:href><d:propstat><d:status>(.*?)</d:status><d:prop><d:getetag>(.*?)</d:getetag><d:creationdate>(.*?)</d:creationdate><d:displayname>(.*?)</d:displayname><d:getlastmodified>(.*?)</d:getlastmodified><d:getcontenttype>(.*?)</d:getcontenttype><d:getcontentlength>(.*?)</d:getcontentlength><d:resourcetype/></d:prop></d:propstat></d:response>@', $xml, $files);
        return array_merge(self::formatList($folders), self::formatList($files));
    }

    /**
     * @param $lists
     * @return array
     */
    public static function formatList($lists)
    {
        $arr = [];
        unset($lists[1][0]);
        foreach ($lists[1] as $key => $file) {
            $arr[] = [
                'path' => $file,
                'name' => $lists[5][$key],
                'create' => $lists[4][$key],
                'modified' => $lists[6][$key],
                'type' => isset($lists[7][$key]) ? $lists[7][$key] : null,
                'size' => isset($lists[8][$key]) ? self::format($lists[8][$key]) : null
            ];
        }
        return $arr;
    }

    /**
     * @param $path
     * @param $local
     * @return bool|int
     */
    public static function download($path, $local)
    {
        $result = self::request('GET', $path);
        return file_put_contents($local, $result);
    }

    /**
     * @param $local
     * @param $remote
     * @return mixed
     */
    public static function upload($local, $remote)
    {
        return self::request('PUT', $remote, [], $local);
    }

    /**
     * @param $curr_path
     * @param $new_path
     * @return mixed
     */
    public static function move($curr_path, $new_path)
    {
        return self::request('MOVE', $curr_path, [
            'Destination: /' . $new_path,
            'Overwrite: F'
        ]);
    }

    /**
     * @param $remote
     * @return mixed
     */
    public static function delete($remote)
    {
        return self::request('DELETE', $remote);
    }

    /**
     * @param $path
     * @return bool
     */
    public static function publish($path)
    {
        self::$posts = '<propertyupdate xmlns="DAV:">
          <set>
            <prop>
              <public_url xmlns="urn:yandex:disk:meta">true</public_url>
            </prop>
          </set>
        </propertyupdate>';
        $xml = self::request('PROPPATCH', $path);
        preg_match('@<public_url xmlns="urn:yandex:disk:meta">(.*?)</public_url>@', $xml, $link);
        if (isset($link[1]))
            return $link[1];
        return false;
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function unpublish($path)
    {
        self::$posts = '<propertyupdate xmlns="DAV:">
          <remove>
            <prop>
              <public_url xmlns="urn:yandex:disk:meta" />
            </prop>
          </remove>
        </propertyupdate>';
        return self::request('PROPPATCH', $path);
    }

    /**
     * @return array
     */
    public static function getUsage()
    {
        self::$posts = '<D:propfind xmlns:D="DAV:">
          <D:prop>
            <D:quota-available-bytes/>
            <D:quota-used-bytes/>
          </D:prop>
        </D:propfind>';
        $xml = self::request('PROPFIND', '', [
            'Depth: 0',
            'Content-Type: application/xml'
        ]);
        preg_match_all('@<d:quota-(available|used)-bytes>([0-9]+)</d:quota-(available|used)-bytes>@', $xml, $usages);
        return [
            'used' => self::format($usages[2][0]),
            'available' => self::format($usages[2][1]),
            'total' => self::format(array_sum($usages[2]))
        ];
    }

    /**
     * https://stackoverflow.com/a/11860664/1599489
     * @param $size
     * @return string
     */
    public static function format($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

}
