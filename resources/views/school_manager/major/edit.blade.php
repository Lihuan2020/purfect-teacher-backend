<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>

@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">
                    <header>在学校 ({{ session('school.name') }}) - 编辑专业: {{ $major->name }}</header>
                </div>
                <div class="card-body " id="bar-parent">
                    <form action="{{ route('school_manager.major.update') }}" method="post">
                        @csrf
                        <input type="hidden" name="major[id]" value="{{ $major->id }}">
                        <input type="hidden" name="major[department_id]" value="{{ $major->department_id }}">
                        <div class="form-group">
                            <label for="major-name-input">专业名称</label>
                            <input required type="text" class="form-control" id="major-name-input" value="{{ $major->name }}" placeholder="专业名称" name="major[name]">
                        </div>
                        <div class="form-group">
                            <label for="major-desc">简介</label>
                            <textarea class="form-control" name="major[description]" id="major-desc" cols="30" rows="10" placeholder="专业简介">{{ $major->description }}</textarea>
                        </div>
                        <?php
                        Button::Print(['id'=>'btnSubmit','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                        ?>&nbsp;
                        <?php
                        Anchor::Print(['text'=>trans('general.return'),'href'=>route('school_manager.department.majors',['uuid'=>$department->id, 'by'=>'department']),'class'=>'pull-right'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection