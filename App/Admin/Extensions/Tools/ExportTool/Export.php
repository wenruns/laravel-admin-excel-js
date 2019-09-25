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

    function __construct()
    {
//        dd(get_class($this));
    }


    protected $options = [
//        'text' => '导出',
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

    /**
     * @return string
     * 自定义的路由
     */
    public function setExportUrl()
    {
        return route('export');
    }

    /**
     * @return string
     * 设置异步请求数据的请求方式
     */
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
        $options['callback'] = json_encode($this->notNull($res, 'result') ? $res['result'] : []);
        $options['script'] = $this->notNull($res, 'script') ? $res['script'] : '';
        $options['statistic'] = json_encode($this->setStatistics());
        return view($this->setView(), $options);
    }


    abstract public function setCallback() : array ; // 设置回调处理
    abstract public function setHead() : array ; // 设置头
    abstract public function setBody() : array ; // 设置字段
    abstract public function setTrans() : array ; // 设置转义字段
    abstract public function setString() : array ;  // 设置专字符串字段
    abstract public function setFileName() : string ; // 设置导出文件名
    abstract public function setField() : array ; // 设置查询字段
    abstract public function setModel() : array ; // 设置模型
    abstract public function setSpecialField() : array ; // 设置特别字段
    abstract public function setStatistics() : array ; // 设置统计字段
    abstract public function setFilterField() : array ; // 设置筛选过滤字段

    /**
     * @return string
     * 设置导出控件类
     */
    public function setExporter()
    {
        return '\\'.get_class($this);
    }

    /**
     * @param Builder $model
     * @return Builder
     * 额外的筛选条件
     */
    public function setFilter(Builder $model)
    {
        return $model;
    }

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
        $model = $this->filter($model);

        $model = $this->setFilter($model);

        $data = $model->get()->toArray();
        return $data;
    }

    /**
     * @param Builder $model
     * @return Builder
     * 过滤筛选条件
     */
    public function filter(Builder $model)
    {
        $filterField = $this->setFilterField();
        $request = request()->toArray();
        foreach ($filterField as $key => $item) {
            if ($this->notNull($request, $key)) {
                if ($this->notNull($item, 'format_value_callback')) {
                    $val = call_user_func($item['format_value_callback'], $request[$key]);
                } else {
                    $val = $request[$key];
                }
                switch ($item['operator']) {
                    case 'between':
                        $start_index = $this->notNull($item, 'start_index') ? $item['start_index'] : 'start';
                        $end_index = $this->notNull($item, 'end_index') ? $item['end_index'] : 'end';
                        if ($this->notNull($val, $start_index) && $this->notNull($val, $end_index)) {
                            $model->whereBetween($item['column'], [$val[$start_index], $val[$end_index]]);
                        } else if ($this->notNull($val, $start_index)) {
                            $model->where($item['column'], '>=', $val[$start_index]);
                        } else if ($this->notNull($val, $end_index)) {
                            $model->where($item['column'], '<=', $val[$end_index]);
                        }
                        break;
                    case 'in':
                        $model->whereIn($item['column'], $val);
                        break;
                    case 'null':
                        $model->whereNull($item['column']);
                        break;
                    case 'notNull':
                        $model->whereNotNull($item['column']);
                        break;
                    case 'like':
                        $model->where($item['column'], 'like', '%'.$val.'%');
                        break;
                    default:
                        $model->where($item['column'], is_callable($item['operator']) ? call_user_func($item['operator'] , $request[$key]) : $item['operator'], $val);
                }
            }
        }

        return $model;
    }


    /**
     * @param $data
     * @param $index
     * @return bool
     * 判断是否为空
     */
    protected function notNull($data, $index)
    {
        return isset($data[$index]) && !empty($data[$index]);
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
                            $model->$key($val['model'], $val['model'].'.'.$val['foreignKey'], $this->notNull($val, 'symbol') ? $val['symbol'] : '=', $this->notNull($val, 'primaryKey') ? $val['primaryKey'] : $val['foreignKey']);
                        }
                    }
                }
            }
        }
        return $model;
    }

}