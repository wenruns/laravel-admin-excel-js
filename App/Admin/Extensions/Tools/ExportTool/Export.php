<?php
/**
 * Created by PhpStorm.
 * User: wen
 * Date: 2019/9/20
 * Time: 11:08
 * Description: 创建导出按钮
 */

namespace App\Admin\Extensions\Tools\ExportTool;

use Illuminate\Database\Eloquent\Builder;

abstract class Export
{
    protected $options = [
        'text' => '导出',
    ];

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 返回视图
     */
    public function toString()
    {
        return $this->createBtn($this->options);
    }

    /**
     * @return string
     * 设置视图
     */
    public function setView()
    {
        return 'tools.exportbtn';
    }

    public function setExportUrl()
    {
        return route('export');
    }

    public function setMethod()
    {
        return 'POST';
    }

    /**
     * @param $options
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 创建导出按钮
     */
    public function createBtn($options)
    {
        $options['toString'] = json_encode($this->setString());
        $options['translations'] = json_encode($this->setTrans());
        $options['head'] = json_encode($this->setHead());
        $options['body'] = json_encode($this->setBody());
        $options['url'] = $this->setExportUrl();
        $options['token'] = csrf_token();
        $options['filename'] =  $this->setFileName();
        $options['type'] = $this->setType();
        $options['exporter'] = base64_encode($this->setExporter());
        $options['model'] = base64_encode(json_encode($this->setModel()));
        $options['method'] = $this->setMethod();
        $options['specialField'] = json_encode($this->setSpecialField());
        $res = $this->setCallback();
        $options['callback'] = json_encode($res['result']);
        $options['script'] = $res['script'];
        $options['statistic'] = json_encode($this->setStatistics());
//        dd($options);
        return view($this->setView(), $options);
    }


    abstract public function setCallback() : array ; // 设置回调处理
    abstract public function setHead() : array ; // 设置头
    abstract public function setBody() : array ; // 设置字段
    abstract public function setTrans() : array ; // 设置转义字段
    abstract public function setString() : array ;  // 设置专字符串字段
    abstract public function setFileName() : string ; // 设置导出文件名
    abstract public function setExporter() : string ; // 设置导出控件
    abstract public function setField() : array ; // 设置查询字段
    abstract public function setModel() : array ; // 设置模型
    abstract public function setFilter(Builder $model) : Builder; // 设置筛选条件
    abstract public function setSpecialField() : array ; // 设置特别字段
    abstract public function setStatistics() : array ; // 设置统计字段


    /**
     * @param $page
     * @param $perPage
     * @return array
     * 获取导出数据
     */
    public function setData($page, $perPage)
    {
        $request = request()->toArray();
        $model = $this->getModel();
        switch ($request['_export']) {
            case 'all': // 全部
                $model->limit($perPage)->offset($page*$perPage);
                break;
            case 'page': // 当前页
            case 'select': // 选择行
                if (isset($request['ids'])) {
                    $model->whereIn('id', $request['ids']);
                }
                break;
            case 'range': // 指定页
                $startRange = $request['range']['start'];
                $endRange = $request['range']['end'];
                $per = $request['perPage'];
                $total = ($endRange - $startRange + 1) * $per;
                $offset = ($startRange-1) * $per;
                if ($total < $perPage) {
                    $perPage = $total;
                }else{
                    $offset += $page * $perPage;
                    $total = $total - $page * $perPage;
                    if ($total < $perPage) {
                        $perPage = $total;
                    }
                }
                $model->limit($perPage)->offset($offset);
                break;
            default:

        }
        request()->offsetUnset('ids');
        request()->offsetUnset('range');
        request()->offsetUnset('perPage');
        request()->offsetUnset('page');
        request()->offsetUnset('exporter');

        $model = $this->setFilter($model);
        $data = $model->get()->toArray();
        return $data;
    }

    /**
     * @return string
     * 设置导出类型
     */
    public function setType()
    {
        return 'xls';
    }

    /**
     * @return int
     * 设置每次查询条数
     */
    public function setPerPage()
    {
        return 3000;
    }

    /**
     * @param int $page
     * @return mixed
     * 返回数据集合
     */
    public function render($page = 0)
    {
        $returnValue['data'] = $this->setData($page, $this->setPerPage());
        if (count($returnValue['data']) < $this->setPerPage()) {
            $returnValue['end'] = true;
        } else {
            $returnValue['end'] = false;
        }
        $returnValue = $this->formatResponse($returnValue);
        return $returnValue;
    }

    public function formatResponse($returnValue)
    {
        return $returnValue;
    }

    /**
     * @return mixed
     * 获取模型
     */
    protected function getModel()
    {
        $modelOptions = json_decode(base64_decode(request('model')), true);
        request()->offsetUnset('model');
        $modelName = $modelOptions['model'];
        $model = $modelName::select($this->setField());
        if (isset($modelOptions['join'])) {
            foreach ($modelOptions['join'] as $key => $item) {
                if(!empty($item)) {
                    foreach ($item as $val) {
                        if (isset($val['model']) && isset($val['foreignKey'])) {
                            $model->$key($val['model'], $val['model'].'.'.$val['foreignKey'], isset($val['symbol'])?$val['symbol']:'=', isset($val['primaryKey']) ? $val['primaryKey']: $val['foreignKey']);
                        }
                    }
                }
            }
        }
        return $model;
    }

}