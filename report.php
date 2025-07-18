<?php
// report.php
include "proses/connect.php";
require_once "proses/report_functions.php";

// Get all saved reports
$reports = getSavedReports();

// Group reports by type (monthly and semester)
$monthlyReports = array_filter($reports, function($report) {
    return $report['type'] === 'monthly';
});

$semesterReports = array_filter($reports, function($report) {
    return $report['type'] === 'semester';
});

?>

<div class="col-lg-9 mt-2">
    <div class="row">
        <div class="card">
            <div class="card-header">
                <h4>Laporan Bulanan</h4>
            </div>
            <div class="card-body">
                <?php if (empty($monthlyReports)): ?>
                    <p class="text-center py-3">Belum ada laporan bulanan yang tersimpan</p>
                <?php else: ?>
                    <?php foreach ($monthlyReports as $report): ?>
                        <div class="btn btn-light w-100 mb-3 d-flex justify-content-between align-items-center print-report" data-report-id="<?php echo $report['id']; ?>" data-report-content='<?php echo htmlspecialchars(json_encode($report['content']), ENT_QUOTES, 'UTF-8'); ?>'>
                            <span>Laporan Bulanan - <?php echo htmlspecialchars($report['period']); ?></span>
                            <span>
                                <i class="bi bi-file-text"></i>
                                <small class="text-muted ms-2"><?php echo date('d/m/Y', strtotime($report['created_at'])); ?></small>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h4>Laporan Semester</h4>
            </div>
            <div class="card-body">
            <?php if (empty($semesterReports)): ?>
                    <p class="text-center py-3">Belum ada laporan semester yang tersimpan</p>
                <?php else: ?>
                    <?php foreach ($semesterReports as $report): ?>
                        <div class="btn btn-light w-100 mb-3 d-flex justify-content-between align-items-center print-report" data-report-id="<?php echo $report['id']; ?>" data-report-content='<?php echo htmlspecialchars(json_encode($report['content']), ENT_QUOTES, 'UTF-8'); ?>'>
                            <span>Laporan Semester - <?php echo htmlspecialchars($report['period']); ?></span>
                            <span>
                                <i class="bi bi-file-text"></i>
                                <small class="text-muted ms-2"><?php echo date('d/m/Y', strtotime($report['created_at'])); ?></small>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add New Report Button -->
<div class="col-lg-9 mt-3">
    <div class="d-flex justify-content-end">
        <a href="?page=add_report" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Laporan Baru
        </a>
    </div>
</div>

<!-- Hidden div for report printing -->
<div id="printable-report-container" style="display: none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event to all report buttons
    const reportButtons = document.querySelectorAll('.print-report');
    reportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            const reportContent = JSON.parse(this.getAttribute('data-report-content'));
            
            // Set the report content to the hidden container
            const printContainer = document.getElementById('printable-report-container');
            printContainer.innerHTML = reportContent;
            
            // Print the report
            const originalContents = document.body.innerHTML;
            document.body.innerHTML = `
                <div style="padding: 20px;">
                    ${printContainer.innerHTML}
                </div>
            `;
            
            window.print();
            
            // Restore the original content and reload the page
            document.body.innerHTML = originalContents;
            window.location.reload();
        });
    });
});
</script>