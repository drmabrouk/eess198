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

    <!-- Navigation Header / Progress Dashboard -->
    <div style="background: #ffffff; padding: 24px 28px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 4px 18px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #2563eb; border: 1px solid #dbeafe;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 800; color: #0f172a;">الخطة الفصلية والسنوية للمدرس</h2>
                    <p style="margin: 0; font-size: 13px; color: #64748b;">إعداد وإدارة الخطط التعليمية والتوزيع الأسبوعي للمناهج الدراسية واعتمادها المباشر</p>
                </div>
            </div>

            <!-- Tab Switching & Print Buttons -->
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=annual_plan&teacher_id=' . $user_id); ?>" target="_blank" class="sm-btn" style="background: #16a34a; color: #fff !important; height: 38px; border-radius: 10px; padding: 0 16px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    🖨️ طباعة الخطة السنوية (PDF)
                </a>
                <button type="button" onclick="switchTermPlanTab('teacher-dashboard')" id="btn-tab-teacher" class="sm-btn" style="background: #2563eb; color: #fff; height: 38px; border-radius: 10px; padding: 0 18px; font-weight: 800; border: none; cursor: pointer;">
                    إعداد وخطة المدرس
                </button>
                <?php if ($is_reviewer): ?>
                    <button type="button" onclick="switchTermPlanTab('reviewer-dashboard')" id="btn-tab-reviewer" class="sm-btn" style="background: #f1f5f9; color: #475569; height: 38px; border-radius: 10px; padding: 0 18px; font-weight: 800; border: 1px solid #cbd5e1; cursor: pointer;">
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
