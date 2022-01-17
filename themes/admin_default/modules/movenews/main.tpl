<!-- BEGIN: main -->
<!-- BEGIN: complete -->
<div class="alert alert-success">
    {MESAGE}
</div>
<!-- END: complete -->
<!-- BEGIN: continue -->
<div class="alert alert-info">{LANG.wait} <img alt="Loading" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/images/load_bar.gif"></div>
<script type="text/javascript">
$(document).ready(function() {
    setTimeout(function() {
        window.location = '{LINK}';
    }, 4000);
});
</script>
<!-- END: continue -->
<!-- BEGIN: data -->
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center text-nowrap" style="width: 15%;">{LANG.stt}</th>
                <th class="text-center text-nowrap" style="width: 50%;">{LANG.news}</th>
                <th class="text-center text-nowrap" style="width: 35%;">{LANG.copystatus}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td>
                    {STT}
                </td>
                <td>
                    {ROW.title}
                </td>
                <td>
                    {ROW.status}
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<!-- END: data -->
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->
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
                    <div class="form-group">
                        <div class="row mb-1">
                            <div class="col-xs-24"><strong>{LANG.cat}</strong></div>
                        </div>
                        <!-- BEGIN: cat1 -->
                        <div class="row">
                            <div class="col-xs-24">
                                {CAT1.space}<label><input type="checkbox" name="fc[]" value="{CAT1.catid}"{CAT1.checked}> {CAT1.title}</label>
                            </div>
                        </div>
                        <!-- END: cat1 -->
                    </div>
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
                    <div class="form-group">
                        <div class="row mb-1">
                            <div class="col-xs-18"><strong>{LANG.cat}</strong></div>
                            <div class="col-xs-6"><strong>{LANG.cat_main}</strong></div>
                        </div>
                        <!-- BEGIN: cat2 -->
                        <div class="row">
                            <div class="col-xs-18">
                                {CAT2.space}<label><input type="checkbox" name="tc[]" value="{CAT2.catid}"{CAT2.checked}> {CAT2.title}</label>
                            </div>
                            <div class="col-xs-6">
                                <input<!-- BEGIN: hide --> class="hidden"<!-- END: hide --> type="radio" name="c2"{CAT2_CHECK} id="cat2-{CAT2.catid}" value="{CAT2.catid}">
                            </div>
                        </div>
                        <!-- END: cat2 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group text-center">
        <button class="btn btn-primary" type="submit">{GLANG.submit}</button>
    </div>
</form>
<script type="text/javascript">
$(document).ready(function() {
    $('[data-toggle="changeMod"]').on('change', function() {
        $('#copyNewForm').trigger('submit');
    });

    function opCat2() {
        var num_cat = $('[name="tc[]"]:checked').length;
        $('[name="tc[]"]').each(function() {
            var catid = $(this).val();
            var cat_choose = $(this).is(':checked');
            if (cat_choose) {
                if (num_cat == 1) {
                    $('#cat2-' + catid).prop('checked', true);
                }
                $('#cat2-' + catid).removeClass('hidden');
            } else {
                $('#cat2-' + catid).prop('checked', false);
                $('#cat2-' + catid).addClass('hidden');
            }
        });
    }
    $('[name="tc[]"]').on('change', function() {
        opCat2();
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
