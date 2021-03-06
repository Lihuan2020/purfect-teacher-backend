<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
?>
@extends('layouts.app')
@section('content')
    <span id="current-manager-uuid" data-id="{{ Auth::user()->uuid }}"></span>
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card">
                <div class="card-head">
                    <header>
                        <span class="text-primary">{{ $plan->title }}</span> -
                        报名表管理: ({{ trans('thumbnail.registrations.'.$requestStatus) }}的报名人数: {{ $registrations->count() }})
                    </header>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="table-padding col-12">
                            <a href="{{ route('teacher.planRecruit.list') }}" class="btn btn-primary">
                                返回招生计划
                            </a>
                            {{-- 根据当前的状态, 显示必要的按钮 --}}
                            @if($requestStatus !== 'waiting')
                                <a class="btn" href="{{ route('teacher.registration.forms.manage',['plan'=>$plan->id,'status'=>'waiting']) }}">
                                    只看新报名记录
                                </a>
                            @endif
                            @if($requestStatus !== 'all')
                                <a class="btn" href="{{ route('teacher.registration.forms.manage',['plan'=>$plan->id,'status'=>'all']) }}">
                                    所有报名记录
                                </a>
                            @endif
                            @if($requestStatus !== 'refused')
                                <a class="btn" href="{{ route('teacher.registration.forms.manage',['plan'=>$plan->id,'status'=>'refused']) }}">
                                    只看已拒绝报名
                                </a>
                            @endif
                            <div id="user-quick-search-app" class="pull-left mr-4">
                                <search-bar
                                        school-id="{{ session('school.id') }}"
                                        scope="registrations"
                                        full-tip="按姓名查找学生的报名表"
                                        v-on:result-item-selected="onItemSelected"
                                ></search-bar>
                            </div>
                        </div>
                        <div class="table-responsive" id="registration-forms-list-app">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>姓名</th>
                                    <th>身份证号</th>
                                    <th>生源</th>
                                    <th>联系方式</th>
                                    <th>中高考分数</th>
                                    <th>状态</th>
                                    <th>服从调剂</th>
                                    <th style="width: 240px;">备注</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($registrations as $form)
                                    @php
                                        /** @var \App\Models\RecruitStudent\RegistrationInformatics $form */
                                    @endphp
                                    <tr>
                                        <td>
                                            <a target="_blank" href="{{ route('teacher.registration.view',['uuid'=>$form->profile->uuid,'plan'=>$plan->id]) }}">
                                                {{ $form->name }}
                                            </a>
                                        </td>
                                        <td>{{ $form->profile->id_number}}</td>
                                        <td>{{ $form->profile->source_place }}</td>
                                        <td>
                                            <p>学生电话: {{ $form->user->mobile }}</p>
                                            <p>电子邮件: <a href="mailto:{{$form->user->email}}">{{ $form->user->email }}</a></p>
                                            <p>家长电话: {{ $form->profile->parent_name }} {{ $form->profile->parent_mobile }}</p>
                                        </td>
                                        <td>{{ $form->profile->examination_score }}</td>
                                        <td>
                                            {{ $form->getStatusText() }}
                                        </td>
                                        <td>
                                        @if($form->isRelocationAllowed())
                                            <span class="text-success">服从调剂</span>
                                        @else
                                            <span class="text-danger">不服从调剂</span>
                                        @endif
                                        </td>
                                        <td>
                                            @if($form->note)
                                                <p>{{ $form->note }}</p>
                                            @endif
                                            @php
                                            $otherForms = $form->user->otherRegistrationForms($plan, $form);
                                            @endphp
                                            @if($otherForms)
                                                <p>该学生的其他报名表</p>
                                                @foreach($otherForms as $key=>$otherForm)
                                                    <p>
                                                        <a target="_blank" href="{{ route('teacher.registration.view',['uuid'=>$otherForm->profile->uuid,'plan'=>$otherForm->plan->id]) }}">
                                                        {{ $key+1 }}: {{ $otherForm->plan->title }}, {{ $otherForm->getStatusText() }}({{ $otherForm->isRelocationAllowed()?'服从调剂':'不服从调剂' }})
                                                        </a>
                                                    </p>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <el-button-group>
                                                @if($form->status === \App\Models\RecruitStudent\RegistrationInformatics::WAITING)
                                                    <el-button size="mini" type="success" icon="el-icon-check" v-on:click="showNotesForm({{ $form->id }}, '{{ $form->name }}')">批准</el-button>
                                                    <el-button size="mini" type="warning" icon="el-icon-close" v-on:click="showRejectForm({{ $form->id }}, '{{ $form->name }}')">拒绝</el-button>
                                                @endif
                                                <el-button size="mini" type="danger" v-on:click="deleteForm({{ $form->id }})">删除<i class="el-icon-delete el-icon--right"></i></el-button>
                                            </el-button-group>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            <el-dialog title="批准报名, 进入录取程序" :visible.sync="showNoteFormFlag">
                                <p>学生姓名: @{{ currentName }}</p>
                                <p>报名专业: {{ $plan->title }} - {{ $plan->year }}年</p>
                                <p class="text-info">一旦批准, 该学生在本校的所有其他报名申请将自动作废.</p>
                                <el-form :model="form">
                                    <el-form-item label="备注">
                                        <el-input type="textarea" v-model="form.note"></el-input>
                                    </el-form-item>
                                </el-form>
                                <div slot="footer" class="dialog-footer">
                                    <el-button @click="showNoteFormFlag = false">取 消</el-button>
                                    <el-button type="primary" @click="submit">确 定</el-button>
                                </div>
                            </el-dialog>

                            <el-dialog title="拒绝此报名申请" :visible.sync="rejectNoteFormFlag">
                                <p class="text-danger">学生姓名: @{{ currentName }}</p>
                                <p class="text-danger">报名专业: {{ $plan->title }} - {{ $plan->year }}年</p>
                                <el-form :model="form">
                                    <el-form-item label="拒绝此报名申请的原因">
                                        <el-input type="textarea" v-model="form.note"></el-input>
                                    </el-form-item>
                                </el-form>
                                <div slot="footer" class="dialog-footer">
                                    <el-button @click="rejectNoteFormFlag = false">取 消</el-button>
                                    <el-button type="primary" @click="submit">确 定</el-button>
                                </div>
                            </el-dialog>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                {{ isset($appendedParams) ? $registrations->appends($appendedParams)->links() : $registrations->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
