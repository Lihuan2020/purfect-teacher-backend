<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>
@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-sm-10 col-md-12 col-xl-12">
            <div class="card-box">
                <div class="card-head">
                    <header>添加</header>
                </div>
                <div class="card-body">
                    <form action="{{ route('manager_wifi.wifiIssueType.add',request()->only('typeid','type_pid')) }}" method="post"  id="add-building-form">
                        @csrf
                        <div class="form-group">
                            <label for="building-name-input">名称</label>
                            <input required type="text" class="form-control" id="building-name-input" value="" placeholder="例如：无法上网" name="infos[type_name]" size="20">
                        </div>
                        <?php
                        Button::Print(['id'=>'btn-create-building','text'=>trans('general.submit')], Button::TYPE_PRIMARY);
                        ?>&nbsp;
                        <?php
                         $returnUrl = route('manager_wifi.wifiIssueType.list',request()->only('type_pid'));
                         Anchor::Print(['text'=>trans('general.return'),'href'=>$returnUrl,'class'=>'pull-right link-return'], Button::TYPE_SUCCESS,'arrow-circle-o-right')
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection