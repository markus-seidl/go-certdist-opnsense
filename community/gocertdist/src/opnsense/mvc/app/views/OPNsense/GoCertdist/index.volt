<script>
    $(document).ready(function() {
        var data_get_map = {'frm_general_settings':"/api/gocertdist/settings/get"};
        mapDataToFormUI(data_get_map).done(function(data){
            formatTokenizersUI();
            $('.selectpicker').selectpicker('refresh');
        });

        $("#saveAct").click(function(){
            $("#saveAct_progress").addClass("fa fa-spinner fa-pulse");
            saveFormToEndpoint(url="/api/gocertdist/settings/set", formid='frm_general_settings',callback_ok=function(){
                // After successful save, reload configuration
                ajaxCall(url="/api/gocertdist/settings/reload", sendData={}, callback=function(data,status) {
                    $("#saveAct_progress").removeClass("fa fa-spinner fa-pulse");
                    if (status == "success" && data['result'] == 'ok') {
                        BootstrapDialog.show({
                            type: BootstrapDialog.TYPE_INFO,
                            title: "{{ lang._('Configuration Saved') }}",
                            message: "{{ lang._('Settings have been saved and configuration reloaded successfully.') }}",
                            draggable: true
                        });
                    } else {
                        BootstrapDialog.show({
                            type: BootstrapDialog.TYPE_WARNING,
                            title: "{{ lang._('Configuration Saved with Warning') }}",
                            message: "{{ lang._('Settings saved but failed to reload configuration: ') }}" + (data['message'] || 'Unknown error'),
                            draggable: true
                        });
                    }
                });
            }, callback_fail=function(){
                $("#saveAct_progress").removeClass("fa fa-spinner fa-pulse");
                BootstrapDialog.show({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: "{{ lang._('Error Saving Configuration') }}",
                    message: "{{ lang._('Failed to save settings. Please check the logs.') }}",
                    draggable: true
                });
            });
        });
    });
</script>

<div class="tab-content content-box tab-content">
    <div id="general" class="tab-pane fade in active">
        <div class="content-box" style="padding-bottom: 1.5em;">
            {{ partial("layout_partials/base_form",['fields':generalForm,'id':'frm_general_settings'])}}
            <div class="col-md-12">
                <hr />
                <button class="btn btn-primary" id="saveAct" type="button"><b>{{ lang._('Save') }}</b> <i id="saveAct_progress"></i></button>
            </div>
        </div>
    </div>
</div>
