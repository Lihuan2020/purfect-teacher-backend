@extends('layouts.h5_app')
@section('content')
    <div id="app-init-data-holder"
         data-school="{{ $user->getSchoolId() }}"
         data-useruuid="{{ $user->uuid }}"
         data-apitoken="{{ $user->api_token }}"
         data-apprequest="1"></div>
    <div id="student-homepage-app" class="school-intro-container">
        <div class="main" v-if="isLoading">
            <p class="text-center text-grey">
                <i class="el-icon-loading"></i>&nbsp;数据加载中 ...
            </p>
        </div>
        <div class="main p-15">
            <div class="pipeline-user-flow-box" v-for="(userFlow, idx) in flowsStartedByMe" :key="idx">
                <el-card shadow="hover" class="pb-3">
                    <h3 style="line-height: 40px;margin-top:0;">
                        <img :src="userFlow.flow.icon" width="40">
                        <span class="pull-right">@{{ userFlow.flow.name }}</span>
                    </h3>
                    <h5>
                        <time class="pull-left" style="font-size: 13px;color: #999;">申请日期: @{{ userFlow.created_at.substring(0, 10) }}</time>
                        &nbsp;当前状态: <span :class="flowResultClass(userFlow.done)">@{{ flowResultText(userFlow.done) }}</span>
                    </h5>
                    <el-button @click="viewMyApplication(userFlow)" type="primary" size="mini" class="button pull-left">查看详情</el-button>
                    <el-button v-if="!userFlow.done" @click="cancelMyApplication(userFlow)" type="danger" size="mini" class="button pull-right">撤销</el-button>
                    <br>
                </el-card>
            </div>
        </div>
    </div>
@endsection
