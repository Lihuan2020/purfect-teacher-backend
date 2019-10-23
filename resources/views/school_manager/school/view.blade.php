<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
use App\User;
?>
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">
                    <header>{{ session('school.name') }}</header>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="row table-padding">
                            <div class="col-12">
                                <a href="{{ route('school_manager.campus.add') }}" class="btn btn-primary" id="btn-create-campus-from-school">
                                    创建新校区 <i class="fa fa-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                                <thead>
                                <tr>
                                    <th>校区名称</th>
                                    <th style="width: 300px;">简介</th>
                                    <th style="width: 400px;">校区设施</th>
                                    <th>学院数</th>
                                    <th>教职工总数</th>
                                    <th>学生总数</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                               
                                @foreach($school->campuses as $index=>$campus)
                                    @php /** @var App\Models\Schools\Campus $campus */ @endphp
                                    <tr>
                                        <td>
                                            {{ $campus->name }}
                                        </td>
                                        <td>{{ $campus->description }}</td>
                                        <td>
                                            <a class="anchor-building-counter" href="{{ route('school_manager.campus.buildings',['uuid'=>$campus->id,'by'=>'campus', 'type'=>\App\Models\Schools\Building::TYPE_CLASSROOM_BUILDING]) }}">
                                                <span class="badge badge-light">教学楼: {{ count($campus->classroomBuildings) }}</span>
                                            </a>
                                            <a class="anchor-building-counter" href="{{ route('school_manager.campus.buildings',['uuid'=>$campus->id,'by'=>'campus', 'type'=>\App\Models\Schools\Building::TYPE_STUDENT_HOSTEL_BUILDING]) }}">
                                                <span class="badge badge-light">学生宿舍楼: {{ count($campus->hostels) }}</span>
                                            </a>
                                            <a class="anchor-building-counter" href="{{ route('school_manager.campus.buildings',['uuid'=>$campus->id,'by'=>'campus', 'type'=>\App\Models\Schools\Building::TYPE_HALL]) }}">
                                                <span class="badge badge-light">会堂/食堂/会议室: {{ count($campus->halls) }}</span>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <a class="anchor-institute-counter" href="{{ route('school_manager.campus.institutes',['uuid'=>$campus->id,'by'=>'campus']) }}">{{ count($campus->institutes) }}</a>
                                        </td>
                                        <td class="text-center">
                                            <a class="employees-counter" href="{{ route('school_manager.campus.users',['type'=>User::TYPE_EMPLOYEE,'uuid'=>$campus->id,'by'=>'campus']) }}">{{ $campus->employeesCount() }}</a>
                                        </td>
                                        <td class="text-center">
                                            <a class="students-counter" href="{{ route('school_manager.campus.users',['type'=>User::TYPE_STUDENT,'uuid'=>$campus->id,'by'=>'campus']) }}">{{ $campus->studentsCount() }}</a>
                                        </td>
                                        <td class="text-center">
                                            {{ Anchor::Print(['text'=>'编辑','class'=>'btn-edit-campus','href'=>route('school_manager.campus.edit',['uuid'=>$campus->id])], Button::TYPE_DEFAULT,'edit') }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
