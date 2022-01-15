<!-- BEGIN: main -->
<form method="get" action="{NV_BASE_ADMINURL}index.php" id="copyNewForm">
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-sm-8">
                    <div class="form-group">
                        <label class="lbltrong">{LANG.from_mod}</label>
                        <select class="form-control" name="fm" data-toggle="changeMod">
                            <option value="">--</option>
                            <!-- BEGIN: mod1 -->
                            <option value="{MOD.key}"{MOD.selected_from}>{MOD.title}</option>
                            <!-- END: mod1 -->
                        </select>
                    </div>
                </div>
                <div class="col-sm-16">
                    <label class="lbltrong">{LANG.cat}</label>
                    <div class=""></div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-sm-8">
                    <div class="form-group">
                        <label class="lbltrong">{LANG.to_mod}</label>
                        <select class="form-control" name="tm" data-toggle="changeMod">
                            <option value="">--</option>
                            <!-- BEGIN: mod2 -->
                            <option value="{MOD.key}"{MOD.selected_to}>{MOD.title}</option>
                            <!-- END: mod2 -->
                        </select>
                    </div>
                </div>
                <div class="col-sm-16">
                    <label class="lbltrong">{LANG.cat}</label>
                    <div class=""></div>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
$(document).ready(function() {
    $('[data-toggle="changeMod"]').on('change', function() {
        $('#copyNewForm').trigger('submit');
    });
});
</script>
<div class="alert alert-info">
    <strong>{LANG.note1}:</strong>
    <ul>
        <li>{LANG.note2}</li>
        <li>{LANG.note3}</li>
    </ul>
</div>
<!-- END: main -->
