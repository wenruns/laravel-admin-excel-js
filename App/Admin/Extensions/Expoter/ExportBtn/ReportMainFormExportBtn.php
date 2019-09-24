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

    public function setSpecialField(): array
    {
        // TODO: Implement setSpecialField() method.
        return [];
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


    public function setExporter(): string
    {
        // TODO: Implement setExporter() method.
        return '\App\Admin\Extensions\Expoter\ExportBtn\ReportMainFormExportBtn';
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


    public function setFilter(Builder $model): Builder
    {
        // TODO: Implement setFilter() method.
        $request = request()->toArray();
        $permissionHelpService = new PermissionHelpService();       //权限
        $model->whereIn('org_code',array_keys($permissionHelpService->getPermissionList()));
        if ($this->notNull($request, 'apply_id')) {
            $model->where('apply_id', '=', $request['apply_id']);
        }
        if ($this->notNull($request, 'custom_name')) {
            $model->where('custom_name', 'like', '%'.$request['custom_name'].'%');
        }
        if ($this->notNull($request, 'sales_name')) {
            $model->where('sales_name', 'like', '%'.$request['sales_name'].'%');
        }
        if ($this->notNull($request, 'building_name')) {
            $model->where('building_name', 'like', '%'.$request['building_name'].'%');
        }
        if ($this->notNull($request, 'bank_name')) {
            $model->where('bank_name', 'like', '%'.$request['bank_name'].'%');
        }

        if ($this->notNull($request, 'sales_channel')) {
            $model->where('sales_channel', 'like', '%'.$request['sales_channel'].'%');
        }

        if ($this->notNull($request, 'org_code')) {
            $model->where('org_code', '=', $request['org_code']);
        }

        if ($this->notNull($request, 'product_id')) {
            $model->whereIn('product_id', $request['product_id']);
        }



        if ($this->notNull($request, 'loan_audit_time')) {
            if ($this->notNull($request['loan_audit_time'], 'start') && $this->notNull($request['loan_audit_time'], 'end')) {
                $model->whereBetween('loan_audit_time', [strtotime($request['loan_audit_time']['start'].' 00:00:00'), strtotime($request['loan_audit_time']['end'].' 23:59:59')]);
            } else if ($this->notNull($request['loan_audit_time'], 'start')) {
                $model->where('loan_audit_time', '>=', strtotime($request['loan_audit_time']['start'].' 00:00:00'));
            } else if ($this->notNull($request['loan_audit_time'], 'end')) {
                $model->where('loan_audit_time', '<=', strtotime($request['loan_audit_time']['end'].' 23:59:59'));
            }
        }

        if ($this->notNull($request, 'sign_time')) {
            if ($this->notNull($request['sign_time'], 'start') && $this->notNull($request['sign_time'], 'end')) {
                $model->whereBetween('sign_time', [strtotime($request['sign_time']['start'].' 00:00:00'), strtotime($request['sign_time']['end'].' 23:59:59')]);
            } else if ($this->notNull($request['sign_time'], 'start')) {
                $model->where('sign_time', '>=', strtotime($request['sign_time']['start'].' 00:00:00'));
            } else if ($this->notNull($request['sign_time'], 'end')) {
                $model->where('sign_time', '<=', strtotime($request['sign_time']['end'].' 23:59:59'));
            }
        }
        if ($this->notNull($request, 'received_commission_time')) {
            if ($this->notNull($request['received_commission_time'], 'start') && $this->notNull($request['received_commission_time'], 'end')) {
                $model->whereBetween('received_commission_time', [strtotime($request['received_commission_time']['start'].' 00:00:00'), strtotime($request['received_commission_time']['end'].' 23:59:59')]);
            } else if ($this->notNull($request['received_commission_time'], 'start')) {
                $model->where('received_commission_time', '>=', strtotime($request['received_commission_time']['start'].' 00:00:00'));
            } else if ($this->notNull($request['received_commission_time'], 'end')) {
                $model->where('received_commission_time', '<=', strtotime($request['received_commission_time']['end'].' 23:59:59'));
            }
        }

        if ($this->notNull($request, 'first_makeloan_time')) {
            if ($this->notNull($request['first_makeloan_time'], 'start') && $this->notNull($request['first_makeloan_time'], 'end')) {
                $model->whereBetween('first_makeloan_time', [strtotime($request['first_makeloan_time']['start'].' 00:00:00'), strtotime($request['received_commission_time']['end'].' 23:59:59')]);
            } else if ($this->notNull($request['first_makeloan_time'], 'start')) {
                $model->where('first_makeloan_time', '>=', strtotime($request['first_makeloan_time']['start'].' 00:00:00'));
            } else if ($this->notNull($request['first_makeloan_time'], 'end')) {
                $model->where('first_makeloan_time', '<=', strtotime($request['first_makeloan_time']['end'].' 23:59:59'));
            }
        }

        if ($this->notNull($request, 'second_makeloan_time')) {
            if ($this->notNull($request['second_makeloan_time'], 'start') && $this->notNull($request['second_makeloan_time'], 'end')) {
                $model->whereBetween('second_makeloan_time', [strtotime($request['second_makeloan_time']['start'].' 00:00:00'), strtotime($request['received_commission_time']['end'].' 23:59:59')]);
            } else if ($this->notNull($request['second_makeloan_time'], 'start')) {
                $model->where('second_makeloan_time', '>=', strtotime($request['second_makeloan_time']['start'].' 00:00:00'));
            } else if ($this->notNull($request['second_makeloan_time'], 'end')) {
                $model->where('second_makeloan_time', '<=', strtotime($request['second_makeloan_time']['end'].' 23:59:59'));
            }
        }
        if ($this->notNull($request, 'third_makeloan_time')) {
            if ($this->notNull($request['third_makeloan_time'], 'start') && $this->notNull($request['third_makeloan_time'], 'end')) {
                $model->whereBetween('third_makeloan_time', [strtotime($request['third_makeloan_time']['start'].' 00:00:00'), strtotime($request['received_commission_time']['end'].' 23:59:59')]);
            } else if ($this->notNull($request['third_makeloan_time'], 'start')) {
                $model->where('third_makeloan_time', '>=', strtotime($request['third_makeloan_time']['start'].' 00:00:00'));
            } else if ($this->notNull($request['third_makeloan_time'], 'end')) {
                $model->where('third_makeloan_time', '<=', strtotime($request['third_makeloan_time']['end'].' 23:59:59'));
            }
        }

        return $model;
    }

    protected function notNull($data, $index)
    {
        return isset($data[$index]) && !empty($data[$index]);
    }

}