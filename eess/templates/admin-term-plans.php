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
        WHERE tp.status IN ('submitted', 'approved', 'returned')
        ORDER BY tp.updated_at DESC LIMIT 50
    ");
}
?>

<div class="sm-content-wrapper" dir="rtl" style="font-family: 'Cairo', sans-serif !important;">

    <!-- Single Consolidated Top Header Banner -->
    <div style="background: #ffffff; padding: 22px 28px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 4px 18px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; background: #eff6ff; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #2563eb; border: 1px solid #dbeafe; flex-shrink: 0;">
                <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h2 style="margin: 0 0 4px 0; font-size: 21px; font-weight: 800; color: #0f172a;">الخطط الفصلية والسنوية للمدرس</h2>
                <p style="margin: 0; font-size: 13px; color: #64748b; font-weight: 500;">إعداد وإدارة الخطط التعليمية والتوزيع الأسبوعي للمناهج الدراسية والاعتماد المباشر</p>
            </div>
        </div>

        <!-- Primary Top Banner Actions -->
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=annual_plan&teacher_id=' . $user_id); ?>" target="_blank" class="sm-btn" style="background: #16a34a; color: #fff !important; height: 40px; border-radius: 12px; padding: 0 16px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                🖨️ طباعة الخطة السنوية
            </a>
            <button type="button" onclick="eessOpenPlanSetupWizard()" class="sm-btn" style="background: #2563eb; color: #fff; height: 40px; border-radius: 12px; padding: 0 18px; font-weight: 800; border: none; cursor: pointer;">
                إعداد وخطة المدرس
            </button>
            <?php if ($is_reviewer): ?>
                <button type="button" onclick="switchTermPlanTab('reviewer-dashboard')" id="btn-tab-reviewer" class="sm-btn" style="background: #f8fafc; color: #334155; height: 40px; border-radius: 12px; padding: 0 18px; font-weight: 800; border: 1px solid #cbd5e1; cursor: pointer;">
                    مراجعة واعتماد الخطط المقدمة
                </button>
            <?php endif; ?>
        </div>
    </div>

        <!-- Annual Progress Overview Bar -->
        <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="font-size: 14px; font-weight: 800; color: #0f172a;">نسبة إنجاز الخطة السنوية الشاملة (العام الأكاديمي <?php echo esc_html($active_academic_year); ?>)</span>
                    <span style="font-size: 12px; color: #64748b; margin-right: 8px;">(<?php echo $completed_terms_count; ?> من <?php echo $terms_in_year; ?> فصول مكتملة)</span>
                </div>
                <div style="font-size: 18px; font-weight: 900; color: #2563eb; font-family: monospace;">
                    <?php echo $annual_completion_pct; ?>%
                </div>
            </div>

            <div style="background: #e2e8f0; height: 10px; border-radius: 20px; overflow: hidden; margin-bottom: 16px;">
                <div style="background: linear-gradient(90deg, #2563eb, #16a34a); height: 100%; width: <?php echo $annual_completion_pct; ?>%; transition: width 0.4s ease;"></div>
            </div>

            <!-- Terms Cards Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
                <?php for ($t = 1; $t <= $terms_in_year; $t++):
                    $p = $plans_by_term[$t] ?? null;
                    $pct = $p ? intval($p->completion_pct) : 0;
                    $st = $p ? $p->status : 'not_started';

                    $badge_bg = '#f1f5f9'; $badge_col = '#64748b'; $st_txt = 'لم تبدأ بعد';
                    if ($st === 'draft') { $badge_bg = '#fef3c7'; $badge_col = '#b45309'; $st_txt = 'مسودة / قيد الإعداد'; }
                    elseif ($st === 'submitted') { $badge_bg = '#e0f2fe'; $badge_col = '#0369a1'; $st_txt = 'مرفوعة للمراجعة'; }
                    elseif ($st === 'approved') { $badge_bg = '#dcfce7'; $badge_col = '#15803d'; $st_txt = '✓ معتمدة رسمياً'; }
                    elseif ($st === 'returned') { $badge_bg = '#fee2e2'; $badge_col = '#b91c1c'; $st_txt = 'مراجعة / طلب تعديل'; }
                ?>
                    <div style="background: #ffffff; padding: 14px 18px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 800; font-size: 13.5px; color: #0f172a;">الفصل الدراسي <?php echo $t; ?></span>
                            <span style="font-size: 11px; padding: 2px 8px; border-radius: 8px; background: <?php echo $badge_bg; ?>; color: <?php echo $badge_col; ?>; font-weight: 800;">
                                <?php echo $st_txt; ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
                            <span>الإنجاز: <strong style="color: #0f172a;"><?php echo $pct; ?>%</strong></span>
                            <?php if ($p): ?>
                                <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=term_plan&plan_id=' . $p->id); ?>" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 800; font-size: 11.5px;">🖨️ طباعة الترم</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- TEACHER TAB: WIZARD & PLAN EDITOR -->
    <div id="panel-teacher-dashboard" class="term-plan-panel" style="display: block;">

        <!-- Setup Header Form -->
        <div style="background: #ffffff; padding: 22px 26px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 800; color: #0f172a;">إعداد الخطة الدراسية وتحديد المواعيد الأكاديمية</h3>

            <form id="eess-term-plan-setup-form">
                <?php wp_nonce_field('sm_term_plan_action', 'sm_nonce'); ?>
                <input type="hidden" name="plan_id" id="tp_plan_id" value="0">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">العام الأكاديمي:</label>
                        <input type="text" name="academic_year" id="tp_academic_year" class="sm-input" value="<?php echo esc_attr($active_academic_year); ?>" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>

                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">المادة الدراسية:</label>
                        <select name="subject" id="tp_subject" class="sm-select" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="">-- اختر المادة --</option>
                            <?php foreach ($unique_subjects as $subj): ?>
                                <option value="<?php echo esc_attr($subj); ?>"><?php echo esc_html($subj); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">الصف الدراسي:</label>
                        <select name="grade" id="tp_grade" class="sm-select" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="">-- اختر الصف --</option>
                            <?php
                            $academic = SM_Settings::get_academic_structure();
                            foreach ($academic['active_grades'] as $g) {
                                echo "<option value='الصف $g'>الصف $g</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">عدد الحصص الأسبوعية:</label>
                        <input type="number" name="weekly_lessons" id="tp_weekly_lessons" class="sm-input" min="1" max="10" value="2" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>

                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">نظام الفصول الأكاديمية:</label>
                        <select name="num_terms" id="tp_num_terms" class="sm-select" onchange="onNumTermsChanged(this.value)" style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="3" selected>ثلاثة فصول دراسية (Term 1, 2, 3)</option>
                            <option value="2">فصلان دراسيان (Term 1, 2)</option>
                        </select>
                    </div>

                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">الفصل المُراد تخطيطه:</label>
                        <select name="term_number" id="tp_term_number" class="sm-select" onchange="onTermNumberSelected(this.value)" style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="1">الفصل الدراسي الأول (Term 1)</option>
                            <option value="2">الفصل الدراسي الثاني (Term 2)</option>
                            <option value="3">الفصل الدراسي الثالث (Term 3)</option>
                        </select>
                    </div>

                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">تاريخ بداية الفصل:</label>
                        <input type="date" name="start_date" id="tp_start_date" onchange="calculateWeeksAuto()" class="sm-input" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 12.5px;">
                    </div>

                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">تاريخ نهاية الفصل:</label>
                        <input type="date" name="end_date" id="tp_end_date" onchange="calculateWeeksAuto()" class="sm-input" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 12.5px;">
                    </div>
                </div>

                <!-- Auto calculated weeks notice -->
                <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 12px 18px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #0369a1; font-weight: 700;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>إجمالي الأسابيع المحسوبة تلقائياً: <strong id="tp_calc_weeks_badge" style="font-size: 15px; color: #2563eb;">0 أسبوعاً</strong></span>
                    </div>
                    <button type="button" onclick="generateWeeklyPlanningFields()" class="sm-btn" style="background: #2563eb; color: #ffffff; height: 38px; border-radius: 10px; padding: 0 20px; font-weight: 800; border: none; cursor: pointer;">
                        توليد وتحديث هيكل الأسابيع تلقائياً
                    </button>
                </div>
            </form>
        </div>

        <!-- Weekly Planning Editor Form -->
        <div id="tp_weekly_editor_container" style="background: #ffffff; padding: 24px 28px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">التوزيع الأسبوعي للدروس والمناهج</h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span id="tp_autosave_indicator" style="font-size: 12px; color: #16a34a; font-weight: 700; display: none;">
                        ✓ تم الحفظ التلقائي كمسودة
                    </span>
                    <button type="button" onclick="saveTermPlanDraft('draft')" class="sm-btn" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; height: 38px; border-radius: 10px; padding: 0 16px; font-weight: 700; cursor: pointer;">
                        حفظ كمسودة
                    </button>
                    <button type="button" onclick="saveTermPlanDraft('submitted')" class="sm-btn" style="background: #16a34a; color: #ffffff; height: 38px; border-radius: 10px; padding: 0 20px; font-weight: 800; border: none; cursor: pointer;">
                        رفع الخطة النهائية للاعتماد
                    </button>
                </div>
            </div>

            <div id="tp_weeks_grid" style="display: flex; flex-direction: column; gap: 16px;">
                <!-- Generated dynamically via JS -->
            </div>
        </div>
    </div>

    <!-- REVIEWER TAB: SUBMITTED PLANS INSPECTION -->
    <?php if ($is_reviewer): ?>
    <div id="panel-reviewer-dashboard" class="term-plan-panel" style="display: none;">
        <div style="background: #ffffff; padding: 24px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; font-weight: 800; color: #0f172a;">سجل الخطط الفصلية المرفوعة للمراجعة والاعتماد</h3>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: right;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0;">المدرس</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0;">المادة / الصف</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0;">الفصل الدراسي</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center;">نسبة الإنجاز</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center;">الحالة</th>
                            <th style="padding: 12px 16px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submitted_plans)): ?>
                            <tr>
                                <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8;">لا توجد خطط فصلية مرفوعة للمراجعة حالياً.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submitted_plans as $sp):
                                $s_bg = '#f1f5f9'; $s_col = '#64748b'; $s_lbl = $sp->status;
                                if ($sp->status === 'submitted') { $s_bg = '#e0f2fe'; $s_col = '#0369a1'; $s_lbl = 'مرفوعة للمراجعة'; }
                                elseif ($sp->status === 'approved') { $s_bg = '#dcfce7'; $s_col = '#15803d'; $s_lbl = 'معتمدة'; }
                                elseif ($sp->status === 'returned') { $s_bg = '#fee2e2'; $s_col = '#b91c1c'; $s_lbl = 'طلب تعديل'; }
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 16px; font-weight: 800; color: #0f172a;"><?php echo esc_html($sp->teacher_name ?: 'مدرس'); ?></td>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #334155;"><?php echo esc_html($sp->subject . ' - ' . $sp->grade); ?></td>
                                    <td style="padding: 12px 16px; font-size: 13px; color: #334155;">الفصل الدراسي <?php echo intval($sp->term_number); ?></td>
                                    <td style="padding: 12px 16px; text-align: center; font-weight: 800; color: #2563eb;"><?php echo intval($sp->completion_pct); ?>%</td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <span style="padding: 3px 10px; border-radius: 8px; background: <?php echo $s_bg; ?>; color: <?php echo $s_col; ?>; font-weight: 800; font-size: 11.5px;">
                                            <?php echo $s_lbl; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <button type="button" onclick="inspectSubmittedPlan(<?php echo htmlspecialchars(json_encode($sp)); ?>)" class="sm-btn" style="background: #2563eb; color: #fff; height: 32px; padding: 0 14px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: none; cursor: pointer;">
                                            معاينة الخطة
                                        </button>
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
    <div style="background: #ffffff; border-radius: 20px; max-width: 720px; width: 100%; border: 1px solid #cbd5e1; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); overflow: hidden; display: flex; flex-direction: column; max-height: 88vh;">
        <!-- Wizard Header -->
        <div style="background: #1e293b; color: #ffffff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-calendar-alt" style="color: #60a5fa; font-size: 22px; width: 22px; height: 22px; margin: 0;"></span>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; font-family: 'Cairo', sans-serif;">معالج إعداد وخطة المدرس (الخطط الفصلية والسنوية)</h3>
            </div>
            <button type="button" onclick="eessClosePlanSetupWizard()" style="background: none; border: none; color: #ffffff; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Progress Steps -->
        <div style="background: #f8fafc; padding: 12px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div id="wiz-step-node-1" class="ref-step-node ref-step-active">1. الإعدادات</div>
            <div id="wiz-step-node-2" class="ref-step-node">2. التواريخ والأسابيع</div>
            <div id="wiz-step-node-3" class="ref-step-node">3. تخطيط الدروس الأسبوعي</div>
            <div id="wiz-step-node-4" class="ref-step-node">4. الاعتماد والتصدير</div>
        </div>

        <!-- Wizard Body Container -->
        <form id="eess-wizard-setup-form" style="padding: 24px; overflow-y: auto; flex: 1;" onsubmit="eessSaveWizardPlanSubmit(event)">
            <!-- Step 1 -->
            <div id="wiz-step-1" class="wiz-step-content" style="display: block;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 800; color: #0f172a;">الخطوة 1: تحديد المادة والصف ونظام الفصول</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">العام الأكاديمي *</label>
                        <input type="text" id="wiz_academic_year" class="sm-input" value="<?php echo esc_attr($active_academic_year); ?>" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">المادة الدراسية *</label>
                        <select id="wiz_subject" class="sm-select" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="">-- اختر المادة --</option>
                            <?php foreach ($unique_subjects as $subj): ?>
                                <option value="<?php echo esc_attr($subj); ?>"><?php echo esc_html($subj); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">الصف الدراسي *</label>
                        <select id="wiz_grade" class="sm-select" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="">-- اختر الصف --</option>
                            <?php
                            foreach ($academic['active_grades'] as $g) {
                                echo "<option value='الصف $g'>الصف $g</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">الحصص الأسبوعية *</label>
                        <input type="number" id="wiz_weekly_lessons" min="1" max="10" value="2" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div id="wiz-step-2" class="wiz-step-content" style="display: none;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 800; color: #0f172a;">الخطوة 2: تحديد تاريخ البداية والنهاية وحساب الأسابيع</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">الفصل الدراسي المراد تخطيطه *</label>
                        <select id="wiz_term_number" class="sm-select" style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="1">الفصل الدراسي الأول (Term 1)</option>
                            <option value="2">الفصل الدراسي الثاني (Term 2)</option>
                            <option value="3">الفصل الدراسي الثالث (Term 3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">عدد الفصول بالعام</label>
                        <select id="wiz_num_terms" class="sm-select" style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 13px;">
                            <option value="3">3 فصول دراسية</option>
                            <option value="2">فصلان دراسيان</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">تاريخ بداية الفصل *</label>
                        <input type="date" id="wiz_start_date" onchange="wizCalculateWeeksAuto()" class="sm-input" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 12.5px;">
                    </div>
                    <div>
                        <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155;">تاريخ نهاية الفصل *</label>
                        <input type="date" id="wiz_end_date" onchange="wizCalculateWeeksAuto()" class="sm-input" required style="height: 40px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 12.5px;">
                    </div>
                </div>

                <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 12px 18px; border-radius: 12px; font-size: 13px; color: #0369a1; font-weight: 700;">
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

            <!-- Wizard Footer Buttons -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <button type="button" id="wiz-prev-btn" onclick="wizNav(-1)" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 18px; font-weight: 700; font-size: 13px; cursor: pointer; display: none;">← السابق</button>
                <div></div>
                <button type="button" id="wiz-next-btn" onclick="wizNav(1)" style="background: #2563eb; color: #ffffff; border: none; border-radius: 10px; padding: 8px 22px; font-weight: 800; font-size: 13.5px; cursor: pointer;">المتابعة للخطوة التالية →</button>
                <button type="submit" id="wiz-submit-btn" style="background: #16a34a; color: #ffffff; border: none; border-radius: 10px; padding: 8px 24px; font-weight: 800; font-size: 13.5px; cursor: pointer; display: none;">رفع الخطة المكتملة للاعتماد</button>
            </div>
        </form>
    </div>
</div>

<!-- Inspection & Approval Modal -->
<div id="tp_inspect_modal" class="sm-modal-overlay" style="display: none;">
    <div class="sm-modal-content" style="max-width: 750px; border-radius: 20px; padding: 28px;">
        <div style="border-bottom: 1px solid #e2e8f0; padding-bottom: 14px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;" id="tp_inspect_title">معاينة ومراجعة الخطة الفصلية</h3>
            <button type="button" onclick="document.getElementById('tp_inspect_modal').style.display='none'" style="background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <div id="tp_inspect_body" style="max-height: 50vh; overflow-y: auto; margin-bottom: 20px; display: flex; flex-direction: column; gap: 12px;">
            <!-- Weeks details populated via JS -->
        </div>

        <?php if ($is_reviewer): ?>
            <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                <label class="sm-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">ملاحظات المراجعة / التوجيه الإداري:</label>
                <textarea id="tp_review_notes_input" class="sm-textarea" rows="2" style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 8px; font-size: 12.5px;"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="submitPlanReview('approved')" class="sm-btn" style="background: #16a34a; color: #fff; height: 38px; border-radius: 10px; padding: 0 20px; font-weight: 800; border: none; cursor: pointer;">
                    ✓ اعتماد الخطة رسمياً
                </button>
                <button type="button" onclick="submitPlanReview('returned')" class="sm-btn" style="background: #dc2626; color: #fff; height: 38px; border-radius: 10px; padding: 0 20px; font-weight: 800; border: none; cursor: pointer;">
                    إعادة للتعديل مع الملاحظات
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let currentPlanData = null;
let currentInspectedPlanId = 0;

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

function eessOpenPlanSetupWizard() {
    wizCurrentStep = 1;
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
                alert('يرجى اختيار المادة والصف الدراسي');
                return;
            }
        }
        if (wizCurrentStep === 2) {
            if (!document.getElementById('wiz_start_date').value || !document.getElementById('wiz_end_date').value) {
                alert('يرجى تحديد بداية ونهاية الفصل الدراسي');
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
