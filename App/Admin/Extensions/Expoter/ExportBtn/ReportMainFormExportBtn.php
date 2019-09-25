<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/23
 * Time: 18:28
 */

namespace App\Admin\Extensions\Expoter\ExportBtn;


use App\Admin\Extensions\Tools\ExportTool\Export;
use App\Models\Common\Department;
use App\Services\Common\OrganizationService;
use App\Services\System\PermissionHelpService;
use Illuminate\Database\Eloquent\Builder;

class ReportMainFormExportBtn extends Export
{
    public function setStatistics(): array
    {
        // TODO: Implement setStatistics() method.
        return [
            'apply_money' => [
                'text' => '进件金额', // 显示的文本
            ],
            'loan_money' => [
                'text' => '审批金额',
            ],
            'receivable_commission' => [
                'text' => '应收服务费'
            ],
            'received_commission' => [
                'text' => '实收服务费'
            ],
            'surplus_commission' => [
                'text' => '剩余未收服务费'
            ]
        ];
    }

    public function setCallback(): array
    {
        // TODO: Implement setCallback() method.
        $script = <<<EOT
    function calculate(data1, data2) {
        return data1-data2;
    }
EOT;
        return [
            'script' => $script,
            'result' => [
                'surplus_commission' => [
                    'callback' => 'calculate',
                    'argument' => [
                        'receivable_commission',
                        'received_commission'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     * 设置筛选中某些字段对应的
     */
    public function setSpecialField(): array
    {
        // TODO: Implement setSpecialField() method.
        return [
//            field_name => new_field_name
        ];
    }
    public function setString(): array
    {
        // TODO: Implement setString() method.
        return [
            'apply_id',
            'idcard_num',
            'bank_loan_id',
        ];
    }

    public function setTrans(): array
    {
        // TODO: Implement setTrans() method.
        return [
            'org_code' => OrganizationService::getOrganizationList(),
            'department_code' => Department::pluck('department_name','department_code'),

        ];
    }

    public function setFileName(): string
    {
        // TODO: Implement setFileName() method.
        return '贷款信息综合报表#'.date('YmdHis');
    }

    public function setHead(): array
    {
        // TODO: Implement setHead() method.
        return [
            '客户编号', '贷款名称', '进件银行', '所属分公司', '业务员', '所属团队', '客户姓名', '身份证号码',
            '进件时间', '进件金额', '审批时间', '审批金额', '审批状态', '服务费率', '应收服务费', '折扣金额', '收取服务费日期' ,
            '实收服务费' , '剩余未收服务费' ,'推单商家', '推单人' ,'推单人电话' ,'应付推单费' ,'支付推单费时间' ,'应付礼品' ,'实际出库礼品（成交礼品）',
            '礼品出库时间','应扣礼品成本' ,'提成点数','提成金额','楼盘','来单渠道','建行申请书编号','分期利率','分期期数'
        ];
    }

    public function setBody(): array
    {
        // TODO: Implement setBody() method.
        return [
            'apply_id', 'product_name', 'bank_name', 'org_code', 'sales_name', 'department_code', 'custom_name',
            'idcard_num', 'sign_time', 'apply_money', 'loan_audit_time', 'loan_money','current_audit_process','fee_rate','receivable_commission',
            '','received_commission_time','received_commission','surplus_commission','reference_company','reference',
            'reference_phone' , '' ,'','','','','','',
            '','building_name','sales_channel','bank_loan_id','loan_rate','loan_periods'
        ];
    }


    public function setModel(): array
    {
        // TODO: Implement setModel() method.
        return [
            'model' => '\App\Models\ReportMainForm',
        ];
    }

    public function setField(): array
    {
        // TODO: Implement setField() method.
        return [
            'apply_id', 'product_name', 'bank_name', 'org_code', 'sales_name', 'department_code', 'custom_name',
            'idcard_num', 'sign_time', 'apply_money', 'loan_audit_time', 'loan_money','current_audit_process','fee_rate','receivable_commission',
            'received_commission_time','received_commission','reference_company','reference',
            'reference_phone','building_name','sales_channel','bank_loan_id','loan_rate','loan_periods'
        ];
    }



    public function setFilterField(): array
    {
        // TODO: Implement setFilterField() method.
        return [
            'apply_id' => [
                'operator' => '=', // = | < | > | <= | >= | <> | like | in | between | null | notNull |
                'column' => 'apply_id',
                'format_value_callback' => function($val){ return $val; },
            ],
            'custom_name' => [
                'operator' => 'like',
                'column' => 'custom_name',
            ],
            'sales_name' => [
                'operator' => 'like',
                'column' => 'sales_name',
            ],
            'building_name' => [
                'operator' => 'like',
                'column' => 'building_name',
            ],
            'bank_name' => [
                'operator' => 'like',
                'column' => 'bank_name',
            ],
            'sales_channel' => [
                'operator' => 'like',
                'column' => 'sales_channel',
            ],
            'org_code' => [
                'operator' => '=',
                'column' => 'org_code',
            ],
            'product_id' => [
                'operator' => 'in',
                'column' => 'product_id',
            ],
            'loan_audit_time' => [
                'operator' => 'between',
                'column' => 'loan_audit_time',
                'start_index' => '',
                'end_index' => '',
                'format_value_callback' => function($val){
                    if ($this->notNull($val, 'start')) {
                        $val['start'] = strtotime($val['start'].' 00:00:00');
                    }
                    if ($this->notNull($val, 'end')) {
                        $val['end'] = strtotime($val['end'].' 00:00:00');
                    }
                    return $val;
                }
            ],
            'sign_time' => [
                'operator' => 'between',
                'column' => 'sign_time',
                'start_index' => '',
                'end_index' => '',
                'format_value_callback' => function($val){
                    if ($this->notNull($val, 'start')) {
                        $val['start'] = strtotime($val['start'].' 00:00:00');
                    }
                    if ($this->notNull($val, 'end')) {
                        $val['end'] = strtotime($val['end'].' 00:00:00');
                    }
                    return $val;
                }
            ],
            'received_commission_time' => [
                'operator' => 'between',
                'column' => 'received_commission_time',
                'start_index' => '',
                'end_index' => '',
                'format_value_callback' => function($val){
                    if ($this->notNull($val, 'start')) {
                        $val['start'] = strtotime($val['start'].' 00:00:00');
                    }
                    if ($this->notNull($val, 'end')) {
                        $val['end'] = strtotime($val['end'].' 00:00:00');
                    }
                    return $val;
                }
            ],
            'first_makeloan_time' => [
                'operator' => 'between',
                'column' => 'first_makeloan_time',
                'start_index' => '',
                'end_index' => '',
                'format_value_callback' => function($val){
//                    dd($val);
//                    dd(request()->toArray());
                    if ($this->notNull($val, 'start')) {
                        $val['start'] = strtotime($val['start'].' 00:00:00');
                    }
                    if ($this->notNull($val, 'end')) {
                        $val['end'] = strtotime($val['end'].' 00:00:00');
                    }
//                    dd($val);

                    return $val;
                }
            ],
            'second_makeloan_time' => [
                'operator' => 'between',
                'column' => 'second_makeloan_time',
                'start_index' => '',
                'end_index' => '',
                'format_value_callback' => function($val){
                    if ($this->notNull($val, 'start')) {
                        $val['start'] = strtotime($val['start'].' 00:00:00');
                    }
                    if ($this->notNull($val, 'end')) {
                        $val['end'] = strtotime($val['end'].' 00:00:00');
                    }
                    return $val;
                }
            ],
            'third_makeloan_time' => [
                'operator' => 'between',
                'column' => 'third_makeloan_time',
                'start_index' => '',
                'end_index' => '',
                'format_value_callback' => function($val){
                    if ($this->notNull($val, 'start')) {
                        $val['start'] = strtotime($val['start'].' 00:00:00');
                    }
                    if ($this->notNull($val, 'end')) {
                        $val['end'] = strtotime($val['end'].' 00:00:00');
                    }
                    return $val;
                }
            ]
        ];
    }


    public function setFilter(Builder $model): Builder
    {
        // TODO: Implement setFilter() method.
        $permissionHelpService = new PermissionHelpService();       //权限
        $model->whereIn('org_code',array_keys($permissionHelpService->getPermissionList()));
        return $model;
    }


}