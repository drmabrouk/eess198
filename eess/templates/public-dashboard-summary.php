<?php
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();
$user_id = $user->ID;
$roles = (array) $user->roles;

$is_admin = in_array('administrator', $roles) || in_array('sm_system_admin', $roles) || current_user_can('manage_options');
$is_principal = in_array('sm_principal', $roles);
$is_supervisor = in_array('sm_supervisor', $roles);
$is_hod = in_array('sm_hod', $roles);
$is_coordinator = in_array('sm_coordinator', $roles);
$is_teacher = in_array('sm_teacher', $roles);
$is_discipline_sup = in_array('sm_discipline_supervisor', $roles);
$is_activities_sup = in_array('sm_activities_supervisor', $roles);
$is_parent = in_array('sm_parent', $roles) || in_array('sm_student', $roles);

$dash_data = SM_DB::get_personalized_dashboard_data($user_id);
?>

<?php if ($is_teacher || $is_coordinator || $is_activities_sup): ?>
    <!-- TEACHER & COORDINATOR PERSONALIZED OPERATIONAL DASHBOARD -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 25px;">

        <!-- 1. Current / Next Lesson Card -->
        <div style="background: #ffffff; border-radius: 16px; padding: 20px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-top: 4px solid #2563eb;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 12px; font-weight: 800; color: #64748b;">الحصة الحالية / القادمة</span>
                <span class="dashicons dashicons-clock" style="color: #2563eb; font-size: 18px;"></span>
            </div>
            <?php if (!empty($dash_data['current_lesson'])):
                $les = $dash_data['current_lesson'];
            ?>
                <div style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 4px;"><?php echo esc_html($les['subject']); ?></div>
                <div style="font-size: 12px; font-weight: 700; color: #2563eb; margin-bottom: 6px;"><?php echo esc_html($les['grade'] . ' (' . $les['section'] . ')'); ?></div>
                <div style="font-size: 11px; color: #64748b;"><?php echo esc_html($les['period']); ?></div>
            <?php else: ?>
                <div style="padding: 15px 0; text-align: center; color: #94a3b8; font-size: 12px; font-weight: 700;">
                    لا توجد حصص مجدولة حالياً لهذا الوقت.
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. Daily Attendance Card -->
        <div style="background: #ffffff; border-radius: 16px; padding: 20px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-top: 4px solid #16a34a;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 12px; font-weight: 800; color: #64748b;">نسبة حضور فصولي اليوم</span>
                <span class="dashicons dashicons-groups" style="color: #16a34a; font-size: 18px;"></span>
            </div>
            <?php $att = $dash_data['attendance_stats'] ?? array('pct' => 100, 'present' => 0, 'absent' => 0, 'excused' => 0, 'total_students' => 0); ?>
            <div style="font-size: 28px; font-weight: 900; color: #16a34a; line-height: 1; margin-bottom: 6px;"><?php echo $att['pct']; ?>%</div>
            <div style="font-size: 11px; color: #64748b; display: flex; gap: 8px;">
                <span>حاضر: <strong><?php echo $att['present']; ?></strong></span>
                <span>غائب: <strong><?php echo $att['absent']; ?></strong></span>
                <span>المجموع: <strong><?php echo $att['total_students']; ?></strong></span>
            </div>
        </div>

        <!-- 3. Tasks Awaiting Evaluation Card -->
        <a href="<?php echo add_query_arg('sm_tab', 'assignments'); ?>" style="text-decoration: none; background: #ffffff; border-radius: 16px; padding: 20px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-top: 4px solid #f59e0b; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 12px; font-weight: 800; color: #64748b;">مهام بانتظار التقييم والمراجعة</span>
                <span class="dashicons dashicons-welcome-write-blog" style="color: #f59e0b; font-size: 18px;"></span>
            </div>
            <?php $tasks = $dash_data['tasks_eval'] ?? array('total' => 0, 'pending_homework' => 0, 'pending_preps' => 0); ?>
            <div style="font-size: 28px; font-weight: 900; color: #d97706; line-height: 1; margin-bottom: 6px;"><?php echo $tasks['total']; ?></div>
            <div style="font-size: 11px; color: #64748b;">واجبات سريعة وتحضيرات بانتظار مراجعتك</div>
        </a>

        <!-- 4. Academic Alerts Card -->
        <a href="<?php echo add_query_arg('sm_tab', 'stats'); ?>" style="text-decoration: none; background: #ffffff; border-radius: 16px; padding: 20px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-top: 4px solid #dc2626; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 12px; font-weight: 800; color: #64748b;">تنبيهات انضباطية وأكاديمية</span>
                <span class="dashicons dashicons-warning" style="color: #dc2626; font-size: 18px;"></span>
            </div>
            <div style="font-size: 28px; font-weight: 900; color: #dc2626; line-height: 1; margin-bottom: 6px;"><?php echo intval($dash_data['academic_alerts_count'] ?? 0); ?></div>
            <div style="font-size: 11px; color: #64748b;">طلاب بحاجة لمتابعة خاصة وسجل سلوكي</div>
        </a>
    </div>

    <!-- Teacher Quick Actions Bar -->
    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 16px; padding: 18px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="font-weight: 800; font-size: 13px; color: #0f172a; display: flex; align-items: center; gap: 6px;">
            <span class="dashicons dashicons-bolt" style="color: #f59e0b;"></span>
            إجراءات سريعة للمعلم:
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?php echo home_url('/attendance/'); ?>" class="sm-btn" style="height: 36px; padding: 0 14px; font-size: 12px; background: #2563eb; color: white !important; text-decoration: none;">تسجيل غياب للحصة الحالية</a>
            <a href="<?php echo add_query_arg('sm_tab', 'grades'); ?>" class="sm-btn" style="height: 36px; padding: 0 14px; font-size: 12px; background: #000; color: white !important; text-decoration: none;">رصد درجات / تقييم جديد</a>
            <a href="<?php echo add_query_arg('sm_tab', 'assignments'); ?>" class="sm-btn" style="height: 36px; padding: 0 14px; font-size: 12px; background: #475569; color: white !important; text-decoration: none;">إضافة واجب / تكليف</a>
            <button type="button" onclick="eessOpenQuickParentNoteModal()" class="sm-btn sm-btn-outline" style="height: 36px; padding: 0 14px; font-size: 12px; background: white;">إرسال ملاحظة لولي الأمر</button>
        </div>
    </div>
<?php else: ?>
    <!-- ADMIN & GENERAL STATS CARD GRID -->
    <div class="sm-card-grid" style="margin-bottom: 30px;">
        <div class="sm-stat-card" style="border-top: 4px solid var(--sm-primary-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">إجمالي الطلاب</div>
            <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-primary-color); line-height: 1.2;"><?php echo esc_html($stats['total_students'] ?? $dash_data['total_students'] ?? 0); ?></div>
        </div>
        <div class="sm-stat-card" style="border-top: 4px solid var(--sm-secondary-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">إجمالي المعلمين</div>
            <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-secondary-color); line-height: 1.2;"><?php echo esc_html($stats['total_teachers'] ?? $dash_data['total_teachers'] ?? 0); ?></div>
        </div>
        <div class="sm-stat-card" style="border-top: 4px solid var(--sm-accent-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">مخالفات اليوم</div>
            <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-accent-color); line-height: 1.2;"><?php echo esc_html($stats['violations_today'] ?? $dash_data['violations_today'] ?? 0); ?></div>
        </div>
        <div class="sm-stat-card" style="border-top: 4px solid var(--sm-dark-color); border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 0.9em; color: var(--sm-text-gray); margin-bottom: 8px; font-weight: 700;">الإجراءات المتخذة</div>
            <div style="font-size: 2.6em; font-weight: 800; color: var(--sm-dark-color); line-height: 1.2;"><?php echo esc_html($stats['total_actions'] ?? 0); ?></div>
        </div>
    </div>
<?php endif; ?>



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
