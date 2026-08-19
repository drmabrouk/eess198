<?php
if (!defined('ABSPATH')) exit;
if (in_array('sm_student', (array)wp_get_current_user()->roles)) {
    echo '<p>يرجى التوجه إلى لوحة المعلومات الخاصة بك.</p>';
    return;
}
?>

<div class="sm-card-grid" style="margin-bottom: 40px;">
    <div class="sm-stat-card" style="border-top: 4px solid var(--sm-primary-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">إجمالي الطلاب</div>
        <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-primary-color); line-height: 1.2;"><?php echo esc_html($stats['total_students'] ?? 0); ?></div>
    </div>
    <div class="sm-stat-card" style="border-top: 4px solid var(--sm-secondary-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">إجمالي المعلمين</div>
        <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-secondary-color); line-height: 1.2;"><?php echo esc_html($stats['total_teachers'] ?? 0); ?></div>
    </div>
    <div class="sm-stat-card" style="border-top: 4px solid var(--sm-accent-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">مخالفات اليوم</div>
        <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-accent-color); line-height: 1.2;"><?php echo esc_html($stats['violations_today'] ?? 0); ?></div>
    </div>
    <div class="sm-stat-card" style="border-top: 4px solid var(--sm-dark-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">الإجراءات المتخذة</div>
        <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-dark-color); line-height: 1.2;"><?php echo esc_html($stats['total_actions'] ?? 0); ?></div>
    </div>
</div>



<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
    <!-- Trends and Categories Charts -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--sm-border-color); position: relative; max-height: 300px; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
            <h3 style="margin:0; font-size: 1.0em;">اتجاهات المخالفات (آخر 30 يوم)</h3>
            <button onclick="smDownloadChart('violationTrendsChart', 'اتجاهات_المخالفات')" class="sm-action-btn" title="تحميل كصورة" style="background:none; border:none; color:var(--sm-text-gray); cursor:pointer;"><span class="dashicons dashicons-download"></span></button>
        </div>
        <div style="height: 180px;"><canvas id="violationTrendsChart"></canvas></div>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--sm-border-color); position: relative; max-height: 300px; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
            <h3 style="margin:0; font-size: 1.0em;">توزيع الأنواع</h3>
            <button onclick="smDownloadChart('violationCategoriesChart', 'توزيع_الأنواع')" class="sm-action-btn" title="تحميل كصورة" style="background:none; border:none; color:var(--sm-text-gray); cursor:pointer;"><span class="dashicons dashicons-download"></span></button>
        </div>
        <div style="height: 180px;"><canvas id="violationCategoriesChart"></canvas></div>
    </div>
</div>





<script>
function smDownloadChart(chartId, fileName) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = fileName + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

(function() {
    window.smCharts = window.smCharts || {};

    const initSummaryCharts = function() {
        if (typeof Chart === 'undefined') {
            setTimeout(initSummaryCharts, 200);
            return;
        }

        const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };
        const severityLabels = <?php echo json_encode(SM_Settings::get_severities()); ?>;

        const createOrUpdateChart = (id, config) => {
            if (window.smCharts[id]) {
                window.smCharts[id].destroy();
            }
            const el = document.getElementById(id);
            if (el) {
                window.smCharts[id] = new Chart(el.getContext('2d'), config);
            }
        };

        // Trends Chart
        createOrUpdateChart('violationTrendsChart', {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($t){ return date('m/d', strtotime($t->date)); }, $stats['trends'] ?? [])); ?>,
                datasets: [{
                    label: 'المخالفات',
                    data: <?php echo json_encode(array_map(function($t){ return $t->count; }, $stats['trends'] ?? [])); ?>,
                    borderColor: '#334155',
                    backgroundColor: 'rgba(51, 65, 85, 0.08)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Categories Chart
        const typeLabels = <?php echo json_encode(SM_Settings::get_violation_types()); ?>;
        createOrUpdateChart('violationCategoriesChart', {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($t) use ($typeLabels){ return $typeLabels[$t->type] ?? $t->type; }, $stats['by_type'] ?? [])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function($t){ return $t->count; }, $stats['by_type'] ?? [])); ?>,
                    backgroundColor: ['#334155', '#475569', '#64748B', '#1E293B', '#94A3B8']
                }]
            },
            options: chartOptions
        });

    };

    if (document.readyState === 'complete') initSummaryCharts();
    else window.addEventListener('load', initSummaryCharts);
})();
</script>
