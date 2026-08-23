<?php
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();
$user_id = $user->ID;
$user_roles = (array) $user->roles;

$is_admin = current_user_can('manage_options') || in_array('administrator', $user_roles) || in_array('sm_system_admin', $user_roles);
$is_reviewer = $is_admin || in_array('sm_principal', $user_roles) || in_array('sm_supervisor', $user_roles) || in_array('sm_coordinator', $user_roles) || in_array('sm_hod', $user_roles);
$is_teacher = in_array('sm_teacher', $user_roles) || $is_admin;

global $wpdb;

// Fetch active term plans for current user or reviewed plans for reviewers
$active_academic_year = '2025/2026';
$all_subjects = SM_DB::get_subjects();
$unique_subjects = array_unique(array_map(function($s){ return is_object($s) ? $s->name : $s; }, $all_subjects));

// Retrieve existing plans for teacher
$teacher_plans = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}sm_term_plans WHERE teacher_id = %d ORDER BY term_number ASC",
    $user_id
));

// Organize teacher plans by term_number (1, 2, 3)
$plans_by_term = array(1 => null, 2 => null, 3 => null);
$completed_terms_count = 0;
$total_completion_sum = 0;
$terms_in_year = 3;

foreach ($teacher_plans as $p) {
    $plans_by_term[$p->term_number] = $p;
    $total_completion_sum += intval($p->completion_pct);
    if ($p->status === 'approved' || $p->completion_pct >= 100) {
        $completed_terms_count++;
    }
    if ($p->num_terms > 0) {
        $terms_in_year = $p->num_terms;
    }
}

$annual_completion_pct = $terms_in_year > 0 ? round($total_completion_sum / $terms_in_year) : 0;
if ($annual_completion_pct > 100) $annual_completion_pct = 100;

// Submitted plans for Reviewers
$submitted_plans = array();
if ($is_reviewer) {
    $submitted_plans = $wpdb->get_results("
        SELECT tp.*, u.display_name as teacher_name
        FROM {$wpdb->prefix}sm_term_plans tp
        LEFT JOIN {$wpdb->users} u ON tp.teacher_id = u.ID
        WHERE tp.status IN ('submitted', 'approved', 'returned', 'rejected', 'draft')
        ORDER BY tp.updated_at DESC LIMIT 100
    ");
}

$arabic_term_names = array(
    1 => 'الفصل الدراسي الأول',
    2 => 'الفصل الدراسي الثاني',
    3 => 'الفصل الدراسي الثالث'
);
?>

<div class="sm-content-wrapper" dir="rtl" style="font-family: 'Cairo', sans-serif !important;">

    <!-- Single Main Banner Header -->
    <div style="background: #ffffff; padding: 20px 24px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 18px; box-shadow: 0 4px 18px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; background: #fef2f2; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #881337; border: 1px solid #fecdd3; flex-shrink: 0;">
                <span class="dashicons dashicons-calendar-alt" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 800; color: #0f172a;">الخطط الفصلية والسنوية للمدرس</h2>
                <p style="margin: 0; font-size: 12.5px; color: #64748b; font-weight: 500;">إعداد وإدارة الخطط التعليمية والتوزيع الأسبوعي للمناهج الدراسية والاعتماد المباشر</p>
            </div>
        </div>

        <!-- Primary Header Actions (Wine-Red, Black, White Button Tokens) -->
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=annual_plan&teacher_id=' . $user_id); ?>" target="_blank" class="sm-btn" style="background: #1e293b; color: #ffffff !important; height: 38px; border-radius: 9999px !important; padding: 0 18px; font-weight: 800; font-size: 12.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: none;">
                <span class="dashicons dashicons-printer" style="font-size: 15px; width: 15px; height: 15px; color: #fff;"></span>
                <span>طباعة الخطة السنوية</span>
            </a>
            <button type="button" onclick="eessOpenPlanSetupWizard(1)" class="sm-btn" style="background: #881337; color: #ffffff !important; height: 38px; border-radius: 9999px !important; padding: 0 20px; font-weight: 800; font-size: 12.5px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span class="dashicons dashicons-plus-alt2" style="font-size: 15px; width: 15px; height: 15px; color: #fff;"></span>
                <span>إعداد وخطة المدرس</span>
            </button>
            <?php if ($is_reviewer): ?>
                <button type="button" onclick="switchTermPlanTab('reviewer-dashboard')" id="btn-tab-reviewer" class="sm-btn sm-btn-outline" style="background: #ffffff; color: #334155 !important; height: 38px; border-radius: 9999px !important; padding: 0 18px; font-weight: 800; font-size: 12.5px; border: 1px solid #cbd5e1; cursor: pointer;">
                    مراجعة واعتماد الخطط المقدمة
                </button>
            <?php endif; ?>
        </div>
    </div>

        <!-- 3 Independent Annual Progress Cards Grid -->
        <div style="background: #ffffff; padding: 20px 24px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.02); margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="font-size: 15px; font-weight: 800; color: #0f172a;">مؤشرات إنجاز الفصول الدراسية المستقلة (العام الأكاديمي <?php echo esc_html($active_academic_year); ?>)</span>
                    <span style="font-size: 12px; color: #64748b; margin-right: 8px;">(<?php echo $completed_terms_count; ?> من 3 فصول مكتملة)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: #881337; background: #fef2f2; padding: 4px 12px; border-radius: 9999px; border: 1px solid #fecdd3;">
                    <span>إجمالي الإنجاز السنوي: <?php echo $annual_completion_pct; ?>%</span>
                </div>
            </div>

            <!-- 3 Completely Independent Term Progress Cards -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <?php for ($t = 1; $t <= 3; $t++):
                    $p = $plans_by_term[$t] ?? null;
                    $pct = $p ? intval($p->completion_pct) : 0;
                    $st = $p ? $p->status : 'not_started';

                    $badge_bg = '#f1f5f9'; $badge_col = '#64748b'; $st_txt = 'لم تبدأ بعد';
                    if ($st === 'draft') { $badge_bg = '#fef3c7'; $badge_col = '#b45309'; $st_txt = 'مسودة / قيد الإعداد'; }
                    elseif ($st === 'submitted') { $badge_bg = '#e0f2fe'; $badge_col = '#0369a1'; $st_txt = 'مرفوعة للمراجعة'; }
                    elseif ($st === 'approved') { $badge_bg = '#dcfce7'; $badge_col = '#15803d'; $st_txt = '✓ معتمدة رسمياً'; }
                    elseif ($st === 'returned') { $badge_bg = '#fee2e2'; $badge_col = '#b91c1c'; $st_txt = 'طلب تعديل'; }
                ?>
                    <div onclick="eessOpenPlanSetupWizard(<?php echo $t; ?>)" style="background: #ffffff; padding: 18px; border-radius: 16px; border: 1.5px solid <?php echo $p ? '#cbd5e1' : '#f1f5f9'; ?>; display: flex; flex-direction: column; gap: 12px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#881337'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='<?php echo $p ? '#cbd5e1' : '#f1f5f9'; ?>'; this.style.transform='translateY(0)';">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 800; font-size: 14px; color: #0f172a;"><?php echo $arabic_term_names[$t]; ?></span>
                            <span style="font-size: 11px; padding: 3px 10px; border-radius: 9999px; background: <?php echo $badge_bg; ?>; color: <?php echo $badge_col; ?>; font-weight: 800;">
                                <?php echo $st_txt; ?>
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div style="background: #f1f5f9; height: 8px; border-radius: 9999px; overflow: hidden;">
                            <div style="background: <?php echo $pct >= 100 ? '#16a34a' : '#881337'; ?>; height: 100%; width: <?php echo $pct; ?>%; transition: width 0.3s ease;"></div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
                            <span>نسبة الإنجاز: <strong style="color: #0f172a; font-size: 13px;"><?php echo $pct; ?>%</strong></span>
                            <span style="color: #881337; font-weight: 800; font-size: 11.5px; display: inline-flex; align-items: center; gap: 4px;">
                                <span><?php echo $p ? 'متابعة / تعديل ➔' : '+ بدء التخطيط'; ?></span>
                            </span>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

    <!-- TEACHER TAB: SUBMITTED PLANS HISTORY -->
    <div id="panel-teacher-dashboard" class="term-plan-panel" style="display: block;">
        <div style="background: #ffffff; padding: 24px 28px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">سجل وأرشيف الخطط الفصلية والسنوية الخاصة بي</h3>
                <button type="button" onclick="eessOpenPlanSetupWizard()" class="sm-btn" style="background: #881337; color: #fff; height: 38px; border-radius: 9999px !important; padding: 0 20px; font-weight: 800; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="dashicons dashicons-plus-alt" style="font-size: 16px; width: 16px; height: 16px; margin: 0;"></span>
                    <span>إنشاء خطة جديدة</span>
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: right;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0;">الفصل الدراسي</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0;">المادة والصف</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0;">تاريخ الفترة</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center;">نسبة إنجاز الترم</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center;">الحالة</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teacher_plans)): ?>
                            <tr>
                                <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8;">لا توجد خطط فصلية أو سنوية مسجلة لك حالياً. اضغط "إعداد وخطة المدرس" للبدء.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teacher_plans as $tp):
                                $s_bg = '#f1f5f9'; $s_col = '#64748b'; $s_lbl = 'مسودة';
                                if ($tp->status === 'submitted') { $s_bg = '#e0f2fe'; $s_col = '#0369a1'; $s_lbl = 'مرفوعة للمراجعة'; }
                                elseif ($tp->status === 'approved') { $s_bg = '#dcfce7'; $s_col = '#15803d'; $s_lbl = 'معتمدة'; }
                                elseif ($tp->status === 'returned') { $s_bg = '#fee2e2'; $s_col = '#b91c1c'; $s_lbl = 'طلب تعديل'; }
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 16px; font-weight: 800; color: #0f172a;">الفصل الدراسي <?php echo intval($tp->term_number); ?></td>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #334155;"><?php echo esc_html($tp->subject . ' - ' . $tp->grade); ?></td>
                                    <td style="padding: 12px 16px; font-size: 12px; color: #64748b;"><?php echo esc_html($tp->start_date . ' إلى ' . $tp->end_date); ?></td>
                                    <td style="padding: 12px 16px; text-align: center; font-weight: 800; color: #2563eb;"><?php echo intval($tp->completion_pct); ?>%</td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <span style="padding: 3px 10px; border-radius: 8px; background: <?php echo $s_bg; ?>; color: <?php echo $s_col; ?>; font-weight: 800; font-size: 11.5px;">
                                            <?php echo $s_lbl; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=term_plan&plan_id=' . $tp->id); ?>" target="_blank" class="sm-btn" style="background: #0284c7; color: #fff !important; height: 32px; padding: 0 14px; border-radius: 8px; font-size: 11.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                            🖨️ طباعة
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- REVIEWER TAB: SUBMITTED PLANS INSPECTION (Auto-Rendered with Tight Gap) -->
    <?php if ($is_reviewer): ?>
    <div id="panel-reviewer-dashboard" class="term-plan-panel" style="display: block; margin-top: 18px;">
        <div style="background: #ffffff; padding: 22px 26px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">

            <!-- Table Header & Live Search Engine Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 14px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-shield" style="color: #881337; font-size: 18px; width: 18px; height: 18px;"></span>
                        <span>سجل الخطط الفصلية المرفوعة للمراجعة والاعتماد المباشر</span>
                    </h3>
                    <p style="margin: 0; font-size: 12px; color: #64748b;">متابعة اعتماد الخطط والموافقة الفورية، رفض، أو طلب تعديلات إدارية</p>
                </div>

                <!-- Professional Live Search Input -->
                <div style="position: relative; width: 280px;">
                    <input type="text" id="eess-reviewer-plans-search" onkeyup="eessFilterReviewerPlansTable()" placeholder="ابحث باسم المدرس، المادة، أو الصف..." style="width: 100%; height: 38px; padding: 0 36px 0 14px; border: 1px solid #cbd5e1; border-radius: 9999px !important; font-size: 12.5px; outline: none; background: #f8fafc;">
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                        <span class="dashicons dashicons-search" style="font-size: 15px; width: 15px; height: 15px;"></span>
                    </span>
                </div>
            </div>

            <!-- Table Wrapper -->
            <div style="overflow-x: auto;">
                <table id="eess-reviewer-plans-table" style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: right;">
                    <thead>
                        <tr style="background: #212121; color: #ffffff;">
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #ffffff; border-radius: 0 10px 0 0;">المدرس والرقم الوظيفي</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #ffffff;">المدرسة والمادة والتسكين</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #ffffff;">الفصل الدراسي</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #ffffff; text-align: center;">نسبة الإنجاز</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #ffffff; text-align: center;">الحالة</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #ffffff; text-align: center; border-radius: 10px 0 0 0;">الإجراءات السريعة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submitted_plans)): ?>
                            <tr>
                                <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 700;">لا توجد خطط فصلية مرفوعة للمراجعة حالياً.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submitted_plans as $sp):
                                $emp_code = get_user_meta($sp->teacher_id, 'eess_employee_number', true) ?: (get_user_meta($sp->teacher_id, 'sm_teacher_id', true) ?: 'EMP-' . $sp->teacher_id);
                                $t_school = get_user_meta($sp->teacher_id, 'eess_school_name', true) ?: 'المدرسة الرئيسية';

                                $s_bg = '#f1f5f9'; $s_col = '#64748b'; $s_lbl = 'مسودة';
                                if ($sp->status === 'submitted') { $s_bg = '#e0f2fe'; $s_col = '#0369a1'; $s_lbl = 'مرفوعة للمراجعة'; }
                                elseif ($sp->status === 'approved') { $s_bg = '#dcfce7'; $s_col = '#15803d'; $s_lbl = 'معتمدة رسمياً'; }
                                elseif ($sp->status === 'returned') { $s_bg = '#fee2e2'; $s_col = '#b91c1c'; $s_lbl = 'طلب تعديل'; }
                                elseif ($sp->status === 'rejected') { $s_bg = '#fef2f2'; $s_col = '#991b1b'; $s_lbl = 'مرفوضة'; }

                                $term_arabic = $arabic_term_names[intval($sp->term_number)] ?? ('الفصل ' . intval($sp->term_number));
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;" class="reviewer-plan-row">
                                    <!-- Teacher Name & Employee ID -->
                                    <td style="padding: 12px 16px;">
                                        <div style="font-weight: 800; font-size: 13.5px; color: #0f172a;"><?php echo esc_html($sp->teacher_name ?: 'مدرس غير محدد'); ?></div>
                                        <div style="font-size: 11px; color: #881337; font-weight: 800; font-family: monospace; margin-top: 3px;">
                                            رقم الموظف: <?php echo esc_html($emp_code); ?>
                                        </div>
                                    </td>

                                    <!-- School, Subject & Grade Pastel Capsules -->
                                    <td style="padding: 12px 16px;">
                                        <div style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px;">🏫 <?php echo esc_html($t_school); ?></div>
                                        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                                            <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; background: #fef2f2; color: #881337; border: 1px solid #fecdd3; font-size: 11px; font-weight: 800;">
                                                <?php echo esc_html($sp->subject); ?>
                                            </span>
                                            <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 800;">
                                                <?php echo esc_html($sp->grade); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Arabic Term Name -->
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 800; color: #334155;">
                                        <?php echo esc_html($term_arabic); ?>
                                    </td>

                                    <!-- Completion Percentage Capsule -->
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <span style="display: inline-flex; padding: 3px 10px; border-radius: 9999px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-weight: 900; font-size: 12px;">
                                            <?php echo intval($sp->completion_pct); ?>%
                                        </span>
                                    </td>

                                    <!-- Expanded Status Capsule -->
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <span style="padding: 3px 10px; border-radius: 9999px; background: <?php echo $s_bg; ?>; color: <?php echo $s_col; ?>; font-weight: 800; font-size: 11px;">
                                            <?php echo $s_lbl; ?>
                                        </span>
                                    </td>

                                    <!-- Quick Action Circular Buttons (Approve, Reject, Modification Request) -->
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                            <!-- Approve Button (Positive Green) -->
                                            <button type="button" onclick="eessDirectReviewPlan(<?php echo $sp->id; ?>, 'approved')" title="اعتماد الخطة رسمياً" style="width: 32px; height: 32px; border-radius: 50% !important; background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                <span class="dashicons dashicons-yes-alt" style="font-size: 16px; width: 16px; height: 16px; margin: 0;"></span>
                                            </button>

                                            <!-- Modification Request Button (Warning Orange/Red) -->
                                            <button type="button" onclick="eessOpenModificationNotesModal(<?php echo $sp->id; ?>, '<?php echo esc_js($sp->teacher_name); ?>')" title="طلب تعديلات وملاحظات" style="width: 32px; height: 32px; border-radius: 50% !important; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                <span class="dashicons dashicons-edit" style="font-size: 15px; width: 15px; height: 15px; margin: 0;"></span>
                                            </button>

                                            <!-- Reject Button (Danger Red) -->
                                            <button type="button" onclick="eessDirectReviewPlan(<?php echo $sp->id; ?>, 'rejected')" title="رفض الخطة" style="width: 32px; height: 32px; border-radius: 50% !important; background: #fee2e2; color: #dc2626; border: 1px solid #fecdd3; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                <span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; margin: 0;"></span>
                                            </button>

                                            <!-- Preview Plan Details Button -->
                                            <button type="button" onclick="inspectSubmittedPlan(<?php echo htmlspecialchars(json_encode($sp)); ?>)" title="معاينة محتوى الخطة" style="width: 32px; height: 32px; border-radius: 50% !important; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                <span class="dashicons dashicons-visibility" style="font-size: 15px; width: 15px; height: 15px; margin: 0;"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Professional Multi-Step Plan Setup Wizard Modal -->
<div id="eess-plan-setup-modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(5px); z-index: 999999; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box; font-family: 'Cairo', sans-serif; direction: rtl;">
    <div style="background: #ffffff; border-radius: 20px; max-width: 820px; width: 100%; border: 1px solid #cbd5e1; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); overflow: hidden; display: flex; flex-direction: column; max-height: 88vh;">
        <!-- Thinner Flush Full-Width Wizard Header Banner with White Icon & Title -->
        <div style="background: #1e293b; color: #ffffff; padding: 14px 22px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-calendar-alt" style="color: #ffffff; font-size: 20px; width: 20px; height: 20px; margin: 0;"></span>
                <h3 style="margin: 0; font-size: 15.5px; font-weight: 800; color: #ffffff; font-family: 'Cairo', sans-serif;">معالج إعداد وخطة المدرس (الخطط الفصلية والسنوية)</h3>
            </div>
            <button type="button" onclick="eessClosePlanSetupWizard()" style="background: none; border: none; color: #ffffff; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Full-Width Balanced RTL Stepper Track -->
        <div style="background: #f8fafc; padding: 14px 24px; border-bottom: 1px solid #e2e8f0; position: relative;">
            <div style="position: absolute; top: 50%; left: 40px; right: 40px; height: 2px; background: #e2e8f0; transform: translateY(-50%); z-index: 1;"></div>
            <div style="position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div id="wiz-step-node-1" class="eess-prep-step-indicator active" style="font-weight: 800; font-size: 11.5px; color: #881337; display: flex; flex-direction: column; align-items: center; gap: 4px; background: #f8fafc; padding: 0 6px;">
                    <span style="background: #881337; color: white; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; box-shadow: 0 0 0 4px #f8fafc;">1</span>
                    <span>الإعدادات المبدئية</span>
                </div>
                <div id="wiz-step-node-2" class="eess-prep-step-indicator" style="font-weight: 700; font-size: 11.5px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 4px; background: #f8fafc; padding: 0 6px;">
                    <span style="background: #e2e8f0; color: #475569; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; box-shadow: 0 0 0 4px #f8fafc;">2</span>
                    <span>التواريخ والأسابيع</span>
                </div>
                <div id="wiz-step-node-3" class="eess-prep-step-indicator" style="font-weight: 700; font-size: 11.5px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 4px; background: #f8fafc; padding: 0 6px;">
                    <span style="background: #e2e8f0; color: #475569; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; box-shadow: 0 0 0 4px #f8fafc;">3</span>
                    <span>تخطيط الدروس الأسبوعي</span>
                </div>
                <div id="wiz-step-node-4" class="eess-prep-step-indicator" style="font-weight: 700; font-size: 11.5px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 4px; background: #f8fafc; padding: 0 6px;">
                    <span style="background: #e2e8f0; color: #475569; width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; box-shadow: 0 0 0 4px #f8fafc;">4</span>
                    <span>الاعتماد والتصدير</span>
                </div>
            </div>
        </div>

        <!-- Wizard Body Container -->
        <form id="eess-wizard-setup-form" style="padding: 24px; overflow-y: auto; flex: 1;" onsubmit="eessSaveWizardPlanSubmit(event)">
            <!-- Step 1 -->
            <div id="wiz-step-1" class="wiz-step-content" style="display: block;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 800; color: #0f172a;">الخطوة 1: تحديد المادة والصف ونظام الفصول</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">العام الأكاديمي *</label>
                        <input type="text" id="wiz_academic_year" class="sm-input" value="<?php echo esc_attr($active_academic_year); ?>" required style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 13px; text-align: right; box-sizing: border-box;">
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">المادة الدراسية *</label>
                        <select id="wiz_subject" class="sm-select" required style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 13px; text-align: right; direction: rtl; box-sizing: border-box;">
                            <option value="">-- اختر المادة --</option>
                            <?php foreach ($unique_subjects as $subj): ?>
                                <option value="<?php echo esc_attr($subj); ?>"><?php echo esc_html($subj); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">الصف الدراسي *</label>
                        <select id="wiz_grade" class="sm-select" required style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 13px; text-align: right; direction: rtl; box-sizing: border-box;">
                            <option value="">-- اختر الصف --</option>
                            <?php
                            foreach ($academic['active_grades'] as $g) {
                                echo "<option value='الصف $g'>الصف $g</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">الحصص الأسبوعية *</label>
                        <input type="number" id="wiz_weekly_lessons" min="1" max="10" value="2" required style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 20px; font-size: 13.5px; font-weight: 800; text-align: right; box-sizing: border-box;">
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div id="wiz-step-2" class="wiz-step-content" style="display: none;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 800; color: #0f172a;">الخطوة 2: تحديد تاريخ البداية والنهاية وحساب الأسابيع</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">الفصل الدراسي المراد تخطيطه *</label>
                        <select id="wiz_term_number" class="sm-select" style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 13px; text-align: right; direction: rtl; box-sizing: border-box;">
                            <option value="1">الفصل الدراسي الأول (Term 1)</option>
                            <option value="2">الفصل الدراسي الثاني (Term 2)</option>
                            <option value="3">الفصل الدراسي الثالث (Term 3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">عدد الفصول بالعام</label>
                        <select id="wiz_num_terms" class="sm-select" style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 13px; text-align: right; direction: rtl; box-sizing: border-box;">
                            <option value="3">3 فصول دراسية</option>
                            <option value="2">فصلان دراسيان</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">تاريخ بداية الفصل *</label>
                        <input type="date" id="wiz_start_date" onchange="wizCalculateWeeksAuto()" class="sm-input" required style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 12.5px; text-align: right; box-sizing: border-box;">
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">تاريخ نهاية الفصل *</label>
                        <input type="date" id="wiz_end_date" onchange="wizCalculateWeeksAuto()" class="sm-input" required style="height: 42px; border-radius: 9999px !important; border: 1px solid #cbd5e1; padding: 0 16px; font-size: 12.5px; text-align: right; box-sizing: border-box;">
                    </div>
                </div>

                <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 14px 18px; border-radius: 12px; font-size: 13px; color: #0369a1; font-weight: 700;">
                    إجمالي الأسابيع المحسوبة تلقائياً للفصل: <strong id="wiz_weeks_count_label" style="color: #2563eb; font-size: 15px;">0 أسابيع</strong>
                </div>
            </div>

            <!-- Step 3 -->
            <div id="wiz-step-3" class="wiz-step-content" style="display: none;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 800; color: #0f172a;">الخطوة 3: التوزيع الأسبوعي للدروس وملخص المحتوى</h4>
                <div id="wiz_weekly_inputs_grid" style="display: flex; flex-direction: column; gap: 14px; max-height: 45vh; overflow-y: auto; padding-right: 5px;">
                    <!-- Generated via JS -->
                </div>
            </div>

            <!-- Step 4 -->
            <div id="wiz-step-4" class="wiz-step-content" style="display: none;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 800; color: #0f172a;">الخطوة 4: مراجعة الخطة والاعتماد / حفظ كمسودة</h4>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 15px; font-size: 13px;">
                    <div style="margin-bottom: 8px;"><strong>المادة والصف:</strong> <span id="wiz_rev_subj_grade">---</span></div>
                    <div style="margin-bottom: 8px;"><strong>تاريخ الفصل:</strong> <span id="wiz_rev_dates">---</span></div>
                    <div style="margin-bottom: 8px;"><strong>عدد الأسابيع المخططة:</strong> <span id="wiz_rev_weeks">---</span></div>
                    <div style="color: #16a34a; font-weight: 700; margin-top: 10px;">✓ يتم حفظ التقدم تلقائياً كمسودة آمنة استعادة البيانات.</div>
                </div>
            </div>

            <!-- Wizard Footer Buttons (Wine-Red, Black & White Buttons) -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <button type="button" id="wiz-prev-btn" onclick="wizNav(-1)" class="sm-btn sm-btn-outline" style="background: #ffffff; color: #475569 !important; border: 1px solid #cbd5e1; border-radius: 9999px !important; padding: 8px 18px; font-weight: 700; font-size: 12.5px; cursor: pointer; display: none;">السابق</button>
                <div></div>
                <button type="button" id="wiz-next-btn" onclick="wizNav(1)" class="sm-btn" style="background: #881337; color: #ffffff !important; border: none; border-radius: 9999px !important; padding: 8px 22px; font-weight: 800; font-size: 13px; cursor: pointer;">المتابعة للخطوة التالية</button>
                <button type="submit" id="wiz-submit-btn" class="sm-btn" style="background: #1e293b; color: #ffffff !important; border: none; border-radius: 9999px !important; padding: 8px 24px; font-weight: 800; font-size: 13px; cursor: pointer; display: none;">رفع الخطة المكتملة للاعتماد</button>
            </div>
        </form>
    </div>
</div>

<!-- Inspection & Approval Modal -->
<div id="tp_inspect_modal" class="sm-modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(5px); z-index: 999999; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box; font-family: 'Cairo', sans-serif; direction: rtl;">
    <div style="background: #ffffff; border-radius: 20px; max-width: 720px; width: 100%; border: 1px solid #cbd5e1; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); overflow: hidden; display: flex; flex-direction: column;">
        <div style="background: #1e293b; color: #ffffff; padding: 16px 22px; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box;">
            <h3 style="margin: 0; font-size: 15.5px; font-weight: 800; color: #ffffff;" id="tp_inspect_title">معاينة ومراجعة الخطة الفصلية</h3>
            <button type="button" onclick="document.getElementById('tp_inspect_modal').style.display='none'" style="background: none; border: none; color: #ffffff; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <div style="padding: 22px;">
            <div id="tp_inspect_body" style="max-height: 48vh; overflow-y: auto; margin-bottom: 18px; display: flex; flex-direction: column; gap: 12px;">
                <!-- Weeks details populated via JS -->
            </div>

            <?php if ($is_reviewer): ?>
                <div style="background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 18px;">
                    <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">ملاحظات المراجعة / التوجيه الإداري:</label>
                    <textarea id="tp_review_notes_input" class="sm-textarea" rows="2" style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 8px; font-size: 12.5px;" placeholder="أدخل الملاحظات المطلوبة للتعديل..."></textarea>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="submitPlanReview('approved')" class="sm-btn" style="background: #16a34a; color: #ffffff !important; height: 38px; border-radius: 9999px !important; padding: 0 20px; font-weight: 800; border: none; cursor: pointer;">
                        ✓ اعتماد الخطة رسمياً
                    </button>
                    <button type="button" onclick="submitPlanReview('returned')" class="sm-btn" style="background: #ea580c; color: #ffffff !important; height: 38px; border-radius: 9999px !important; padding: 0 18px; font-weight: 800; border: none; cursor: pointer;">
                        إعادة للتعديل مع الملاحظات
                    </button>
                    <button type="button" onclick="submitPlanReview('rejected')" class="sm-btn" style="background: #dc2626; color: #ffffff !important; height: 38px; border-radius: 9999px !important; padding: 0 18px; font-weight: 800; border: none; cursor: pointer;">
                        رفض الخطة
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let currentPlanData = null;
let currentInspectedPlanId = 0;

function eessFilterReviewerPlansTable() {
    const q = document.getElementById('eess-reviewer-plans-search').value.trim().toLowerCase();
    const rows = document.querySelectorAll('.reviewer-plan-row');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function eessDirectReviewPlan(planId, reviewStatus) {
    if (!planId) return;
    const confirmMsg = reviewStatus === 'approved' ? 'هل أنت متأكد من اعتماد هذه الخطة رسمياً؟' : 'هل أنت متأكد من تغيير حالة هذه الخطة؟';
    if (!confirm(confirmMsg)) return;

    const formData = new FormData();
    formData.append('action', 'sm_review_term_plan');
    formData.append('plan_id', planId);
    formData.append('review_status', reviewStatus);
    formData.append('sm_nonce', '<?php echo wp_create_nonce("sm_term_plan_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            if (typeof smShowNotification === 'function') {
                smShowNotification(reviewStatus === 'approved' ? 'تم اعتماد الخطة الفصلية بنجاح' : 'تم تحديث حالة الخطة بنجاح');
            }
            setTimeout(() => location.reload(), 500);
        } else {
            if (typeof smShowNotification === 'function') {
                smShowNotification('خطأ: ' + (res.data || 'تعذر معالجة الطلب'), true);
            }
        }
    });
}

function eessOpenModificationNotesModal(planId, teacherName) {
    currentInspectedPlanId = planId;
    document.getElementById('tp_inspect_title').innerText = 'طلب تعديل وملاحظات على خطة: ' + teacherName;
    document.getElementById('tp_inspect_body').innerHTML = '<p style="color:#64748b; font-size:12.5px;">يرجى كتابة ملاحظات التعديل المطلوبة أدناه ثم الضغط على "إعادة للتعديل مع الملاحظات".</p>';
    document.getElementById('tp_inspect_modal').style.display = 'flex';
}

function switchTermPlanTab(tabKey) {
    document.querySelectorAll('.term-plan-panel').forEach(p => p.style.display = 'none');
    document.getElementById('panel-' + tabKey).style.display = 'block';

    const btnTeacher = document.getElementById('btn-tab-teacher');
    const btnReviewer = document.getElementById('btn-tab-reviewer');

    if (tabKey === 'teacher-dashboard') {
        if (btnTeacher) { btnTeacher.style.background = '#2563eb'; btnTeacher.style.color = '#ffffff'; }
        if (btnReviewer) { btnReviewer.style.background = '#f1f5f9'; btnReviewer.style.color = '#475569'; }
    } else {
        if (btnTeacher) { btnTeacher.style.background = '#f1f5f9'; btnTeacher.style.color = '#475569'; }
        if (btnReviewer) { btnReviewer.style.background = '#2563eb'; btnReviewer.style.color = '#ffffff'; }
    }
}

function onNumTermsChanged(num) {
    const termSelect = document.getElementById('tp_term_number');
    termSelect.innerHTML = '';
    for (let i = 1; i <= parseInt(num); i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.innerText = 'الفصل الدراسي ' + (i === 1 ? 'الأول (Term 1)' : (i === 2 ? 'الثاني (Term 2)' : 'الثالث (Term 3)'));
        termSelect.appendChild(opt);
    }
}

function onTermNumberSelected(tNum) {
    // Optionally auto-fill dates or load draft for term
}

function calculateWeeksAuto() {
    const sDate = document.getElementById('tp_start_date').value;
    const eDate = document.getElementById('tp_end_date').value;
    const badge = document.getElementById('tp_calc_weeks_badge');

    if (sDate && eDate) {
        const t1 = new Date(sDate).getTime();
        const t2 = new Date(eDate).getTime();
        if (t2 >= t1) {
            const days = Math.floor((t2 - t1) / (1000 * 60 * 60 * 24));
            const weeks = Math.max(1, Math.ceil(days / 7));
            badge.innerText = weeks + ' أسبوعاً';
            return weeks;
        }
    }
    badge.innerText = '0 أسبوعاً';
    return 0;
}

function generateWeeklyPlanningFields() {
    const weeks = calculateWeeksAuto();
    if (weeks <= 0) {
        alert('يرجى تحديد تواريخ بداية ونهاية الفصل الصحيحة أولاً.');
        return;
    }

    const container = document.getElementById('tp_weeks_grid');
    container.innerHTML = '';

    for (let i = 1; i <= weeks; i++) {
        const weekCard = document.createElement('div');
        weekCard.style.cssText = 'background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px;';

        weekCard.innerHTML = `
            <div style="font-size: 13.5px; font-weight: 800; color: #2563eb; display: flex; align-items: center; justify-content: space-between;">
                <span>الأسبوع ${i}</span>
                <span style="font-size: 11px; color: #64748b; font-weight: 600;">تخطيط الحصص الأسبوعية</span>
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">عنوان الدرس / الموضوع الرئيسي:</label>
                <input type="text" name="weeks[${i}][title]" class="sm-input tp-week-input" oninput="triggerAutoSaveDebounced()" style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 12.5px; width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">ملخص المحتوى والأنشطة المقترحة:</label>
                <textarea name="weeks[${i}][summary]" class="sm-textarea tp-week-input" oninput="triggerAutoSaveDebounced()" rows="2" style="border-radius: 8px; border: 1px solid #cbd5e1; padding: 8px; font-size: 12.5px; width: 100%;"></textarea>
            </div>
        `;
        container.appendChild(weekCard);
    }

    document.getElementById('tp_weekly_editor_container').style.display = 'block';
}

let autoSaveTimer = null;
function triggerAutoSaveDebounced() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        saveTermPlanDraft('draft', true);
    }, 2000);
}

function saveTermPlanDraft(targetStatus = 'draft', isSilent = false) {
    const form = document.getElementById('eess-term-plan-setup-form');
    const formData = new FormData(form);

    // Append weekly inputs
    document.querySelectorAll('.tp-week-input').forEach(input => {
        formData.append(input.name, input.value);
    });

    formData.append('action', 'sm_save_term_plan');
    formData.append('status', targetStatus);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById('tp_plan_id').value = res.data.plan_id;
            const indicator = document.getElementById('tp_autosave_indicator');
            if (indicator) {
                indicator.style.display = 'inline-block';
                setTimeout(() => indicator.style.display = 'none', 3000);
            }
            if (!isSilent) {
                if (typeof smShowNotification === 'function') {
                    smShowNotification(targetStatus === 'submitted' ? 'تم رفع الخطة الفصلية للاعتماد بنجاح' : 'تم حفظ المسودة بنجاح');
                }
            }
        }
    });
}

let wizCurrentStep = 1;

function eessOpenPlanSetupWizard(termNum = 1) {
    wizCurrentStep = 1;
    if (document.getElementById('wiz_term_number')) {
        document.getElementById('wiz_term_number').value = termNum;
    }
    updateWizardUI();
    document.getElementById('eess-plan-setup-modal').style.display = 'flex';
}

function eessClosePlanSetupWizard() {
    document.getElementById('eess-plan-setup-modal').style.display = 'none';
}

function wizCalculateWeeksAuto() {
    const sDate = document.getElementById('wiz_start_date').value;
    const eDate = document.getElementById('wiz_end_date').value;
    const label = document.getElementById('wiz_weeks_count_label');

    if (sDate && eDate) {
        const t1 = new Date(sDate).getTime();
        const t2 = new Date(eDate).getTime();
        if (t2 >= t1) {
            const days = Math.floor((t2 - t1) / (1000 * 60 * 60 * 24));
            const weeks = Math.max(1, Math.ceil(days / 7));
            label.innerText = weeks + ' أسابيع';
            return weeks;
        }
    }
    label.innerText = '0 أسابيع';
    return 0;
}

function wizNav(dir) {
    if (dir === 1) {
        if (wizCurrentStep === 1) {
            if (!document.getElementById('wiz_subject').value || !document.getElementById('wiz_grade').value) {
                if (typeof smShowNotification === 'function') {
                    smShowNotification('يرجى اختيار المادة والصف الدراسي قبل المتابعة', true);
                } else {
                    alert('يرجى اختيار المادة والصف الدراسي قبل المتابعة');
                }
                return;
            }
        }
        if (wizCurrentStep === 2) {
            if (!document.getElementById('wiz_start_date').value || !document.getElementById('wiz_end_date').value) {
                if (typeof smShowNotification === 'function') {
                    smShowNotification('يرجى تحديد بداية ونهاية الفصل الدراسي قبل المتابعة', true);
                } else {
                    alert('يرجى تحديد بداية ونهاية الفصل الدراسي قبل المتابعة');
                }
                return;
            }
            generateWizWeeklyFields();
        }
    }

    wizCurrentStep += dir;
    if (wizCurrentStep < 1) wizCurrentStep = 1;
    if (wizCurrentStep > 4) wizCurrentStep = 4;

    if (wizCurrentStep === 4) {
        document.getElementById('wiz_rev_subj_grade').innerText = document.getElementById('wiz_subject').value + ' - ' + document.getElementById('wiz_grade').value;
        document.getElementById('wiz_rev_dates').innerText = document.getElementById('wiz_start_date').value + ' إلى ' + document.getElementById('wiz_end_date').value;
        document.getElementById('wiz_rev_weeks').innerText = wizCalculateWeeksAuto() + ' أسابيع';
    }

    updateWizardUI();
}

function updateWizardUI() {
    for (let i = 1; i <= 4; i++) {
        document.getElementById('wiz-step-' + i).style.display = (i === wizCurrentStep) ? 'block' : 'none';
        const node = document.getElementById('wiz-step-node-' + i);
        if (node) {
            if (i === wizCurrentStep) node.classList.add('ref-step-active');
            else node.classList.remove('ref-step-active');
        }
    }

    document.getElementById('wiz-prev-btn').style.display = (wizCurrentStep > 1) ? 'inline-block' : 'none';
    document.getElementById('wiz-next-btn').style.display = (wizCurrentStep < 4) ? 'inline-block' : 'none';
    document.getElementById('wiz-submit-btn').style.display = (wizCurrentStep === 4) ? 'inline-block' : 'none';
}

function generateWizWeeklyFields() {
    const weeks = wizCalculateWeeksAuto();
    const grid = document.getElementById('wiz_weekly_inputs_grid');
    grid.innerHTML = '';

    for (let i = 1; i <= weeks; i++) {
        const card = document.createElement('div');
        card.style.cssText = 'background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 8px;';
        card.innerHTML = `
            <div style="font-size: 13px; font-weight: 800; color: #2563eb;">الأسبوع ${i}</div>
            <div>
                <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">عنوان الدرس والموضوع الرئيسية:</label>
                <input type="text" name="wiz_weeks[${i}][title]" class="sm-input wiz-week-input" placeholder="عنوان الدرس..." style="height: 38px; border-radius: 8px; padding: 0 10px; font-size: 12.5px; width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">ملخص المحتوى والأنشطة:</label>
                <textarea name="wiz_weeks[${i}][summary]" class="sm-textarea wiz-week-input" rows="2" placeholder="ملخص المحتوى الأسبوعي..." style="border-radius: 8px; padding: 8px; font-size: 12.5px; width: 100%;"></textarea>
            </div>
        `;
        grid.appendChild(card);
    }
}

function eessSaveWizardPlanSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('wiz-submit-btn');
    btn.disabled = true;
    btn.innerText = 'جاري الحفظ والرفع...';

    const formData = new FormData();
    formData.append('action', 'sm_save_term_plan');
    formData.append('sm_nonce', '<?php echo wp_create_nonce("sm_term_plan_action"); ?>');
    formData.append('academic_year', document.getElementById('wiz_academic_year').value);
    formData.append('subject', document.getElementById('wiz_subject').value);
    formData.append('grade', document.getElementById('wiz_grade').value);
    formData.append('weekly_lessons', document.getElementById('wiz_weekly_lessons').value);
    formData.append('num_terms', document.getElementById('wiz_num_terms').value);
    formData.append('term_number', document.getElementById('wiz_term_number').value);
    formData.append('start_date', document.getElementById('wiz_start_date').value);
    formData.append('end_date', document.getElementById('wiz_end_date').value);
    formData.append('status', 'submitted');

    document.querySelectorAll('.wiz-week-input').forEach(input => {
        formData.append(input.name.replace('wiz_weeks', 'weeks'), input.value);
    });

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerText = 'رفع الخطة المكتملة للاعتماد';
        if (res.success) {
            alert('تم إعداد ورفع الخطة الفصلية/السنوية بنجاح وبانتظار اعتماد المشرف.');
            eessClosePlanSetupWizard();
            setTimeout(() => location.reload(), 500);
        } else {
            alert('خطأ: ' + (res.data || 'تعذر حفظ البيانات'));
        }
    });
}

function inspectSubmittedPlan(plan) {
    currentInspectedPlanId = plan.id;
    document.getElementById('tp_inspect_title').innerText = 'معاينة خطة: ' + plan.teacher_name + ' - ' + plan.subject + ' (' + plan.grade + ')';

    const body = document.getElementById('tp_inspect_body');
    body.innerHTML = '';

    let weeks = {};
    try {
        weeks = JSON.parse(plan.weeks_data || '{}');
    } catch(e) {}

    Object.keys(weeks).forEach(wNum => {
        const item = weeks[wNum];
        const card = document.createElement('div');
        card.style.cssText = 'background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 10px;';
        card.innerHTML = `
            <div style="font-weight: 800; font-size: 13px; color: #2563eb; margin-bottom: 4px;">الأسبوع ${wNum}: ${item.title || 'بدون عنوان'}</div>
            <p style="margin: 0; font-size: 12px; color: #334155; line-height: 1.5;">${item.summary || 'لا يوجد ملخص مسجل'}</p>
        `;
        body.appendChild(card);
    });

    document.getElementById('tp_inspect_modal').style.display = 'flex';
}

function submitPlanReview(status) {
    if (!currentInspectedPlanId) return;

    const notes = document.getElementById('tp_review_notes_input').value;
    const formData = new FormData();
    formData.append('action', 'sm_review_term_plan');
    formData.append('plan_id', currentInspectedPlanId);
    formData.append('review_status', status);
    formData.append('review_notes', notes);
    formData.append('sm_nonce', '<?php echo wp_create_nonce("sm_term_plan_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            if (typeof smShowNotification === 'function') smShowNotification('تم مراجعة الخطة بنجاح');
            document.getElementById('tp_inspect_modal').style.display = 'none';
            setTimeout(() => location.reload(), 600);
        }
    });
}
</script>
