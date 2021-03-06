<el-row>
    <el-col :span="15">
        <el-form-item label="部门">
            <el-cascader style="width: 90%;" :props="props" v-model="node.organizations"></el-cascader>
        </el-form-item>
        <el-form-item label="当前部门" v-if="organizationsTabArrayWhenEdit.length > 0">
            <el-tag
                    class="mr-2"
                    :key="idx"
                    v-for="(tag, idx) in organizationsTabArrayWhenEdit"
                    closable
                    :disable-transitions="false"
                    @close="handleOrganizationTagClose(idx)">
                @{{ tag }}
            </el-tag>
        </el-form-item>
        <el-form-item label="部门角色">
            <el-checkbox-group v-model="node.titles">
                <el-checkbox label="{{ \App\Utils\Misc\Contracts\Title::ALL_TXT }}"></el-checkbox>
                @foreach(\App\Models\Schools\Organization::AllTitles() as $title)
                    <el-checkbox label="{{ $title }}"></el-checkbox>
                @endforeach
            </el-checkbox-group>
        </el-form-item>
    </el-col>
    <el-col :span="1">
        <p class="text-center text-info" style="margin-top: 50px;">或</p>
    </el-col>
    <el-col :span="8">
        <el-form-item label="目标用户">
            <el-checkbox-group v-model="node.handlers">
                <el-checkbox label="教师"></el-checkbox>
                <el-checkbox label="职工"></el-checkbox>
                <br>
                <el-checkbox label="学生"></el-checkbox>
            </el-checkbox-group>
        </el-form-item>
    </el-col>
</el-row>
<el-divider style="margin-top:0;"></el-divider>
<h5 class="text-center text-danger">下一步由谁来审核或接手: (如果是最后一步则无需指定)</h5>

<el-row>
    <el-col :span="24">
        <el-form-item label="审核角色">
            <el-checkbox-group v-model="node.notice_to">
                @foreach(\App\Models\Pipeline\Flow\Handler::HigherLevels() as $roleName)
                    <el-checkbox label="{{ $roleName }}"></el-checkbox>
                @endforeach
            </el-checkbox-group>
        </el-form-item>
    </el-col>
</el-row>
<el-row>
    <el-col :span="14">
        <el-form-item label="说明">
            <el-input type="textarea" placeholder="必填: 例如您可以详细描述, 如果要发起本流程, 需要具备的条件, 可能需要提交的文档等" rows="6" v-model="node.description"></el-input>
        </el-form-item>
    </el-col>
    <el-col :span="10">
        <el-form-item label="选择附件">
            <el-button type="primary" icon="el-icon-document" v-on:click="showFileManagerFlag=true">选择附件</el-button>
            <ul style="padding-left: 0;">
                <li v-for="(a, idx) in node.attachments" :key="idx">
                    <p style="margin-bottom: 0;">
                        <span>@{{ a.file_name }}</span>&nbsp;<el-button v-on:click="dropAttachment(idx, a)" type="text" style="color: red">删除</el-button>
                    </p>
                </li>
            </ul>
        </el-form-item>
    </el-col>
</el-row>

