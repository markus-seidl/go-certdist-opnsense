<script>
    $(document).ready(function() {
        var grid = $("#grid-log").UIBootgrid({
            url: "/api/gocertdist/logfile/search",
            search: false,
            rowCount: [10, 25, 50, 100, -1],
            formatters: {}
        });

        // Refresh button functionality
        $("#refresh-log").click(function() {
            grid.bootgrid("reload");
        });
    });
</script>

<div class="tab-content content-box tab-content">
    <div id="logfile" class="tab-pane fade in active">
        <div class="content-box-main">
            <div class="row">
                <div class="col-md-12">
                    <p><strong>{{ lang._('CertDist Log File') }}</strong></p>
                    <p>{{ lang._('This page displays the contents of the CertDist log file (<code>/var/log/certdist.log</code>).') }}</p>
                    <button class="btn btn-primary" id="refresh-log"><i class="fa fa-refresh"></i> {{ lang._('Refresh') }}</button>
                    <hr/>
                    <table id="grid-log" class="table table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th data-column-id="timestamp" data-type="string" data-width="14em">{{ lang._('Timestamp') }}</th>
                            <th data-column-id="message" data-type="string">{{ lang._('Message') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
