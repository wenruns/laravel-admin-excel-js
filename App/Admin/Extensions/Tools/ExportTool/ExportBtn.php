<?php
/**
 * Created by PhpStorm.
 * User: wen
 * Date: 2019/9/20
 * Time: 11:08
 * Description: 导出路由
 */

namespace App\Admin\Extensions\Tools\ExportTool;

class ExportBtn
{
    public function export()
    {
        try {
            $model = base64_decode(request('exporter'));
            $obj = new $model();
            $data = $obj->render(request('page'));
            return json_encode($data);
        } catch (\Exception $e) {
            if (env('APP_DEBUG', true)) {
                dd($e);
            }
        }
    }
}