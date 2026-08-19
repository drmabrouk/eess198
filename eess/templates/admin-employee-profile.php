<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$roles = (array) $current_user->roles;
$is_admin = in_array('administrator', $roles) || current_user_can('manage_options');
$is_sys_admin = in_array('sm_system_admin', $roles);
$is_hr = in_array('sm_hr', $roles) || current_user_can('manage_hr');

// Determine the target employee
$target_user_id = $current_user->ID;
if (($is_admin || $is_sys_admin || $is_hr) && isset($_GET['employee_id'])) {
    $target_user_id = intval($_GET['employee_id']);
}

// Handle profile photo upload with HR approval workflow
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_FILES['profile_photo_upload'])) {
    if (isset($_POST['eess_photo_nonce']) && wp_verify_nonce($_POST['eess_photo_nonce'], 'eess_profile_photo_upload')) {
        if (!empty($_FILES['profile_photo_upload']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_id = media_handle_upload('profile_photo_upload', 0);
            if (!is_wp_error($attachment_id)) {
                $photo_url = wp_get_attachment_url($attachment_id);
                update_user_meta($target_user_id, 'eess_pending_profile_photo', $photo_url);
                echo '<div style="background:#fffbeb; color:#b45309; padding:15px; border-radius:8px; border:1px solid #fef3c7; font-weight:700; margin-bottom:20px; font-family:\'Cairo\', sans-serif; text-align:right;">⚠️ تم رفع الصورة الشخصية بنجاح وهي معلقة بانتظار موافقة واعتماد قسم الموارد البشرية (HR).</div>';
            } else {
                echo '<div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; border:1px solid #fca5a5; font-weight:700; margin-bottom:20px; font-family:\'Cairo\', sans-serif; text-align:right;">❌ خطأ في رفع الصورة: ' . esc_html($attachment_id->get_error_message()) . '</div>';
            }
        }
    }
}

$u = get_userdata($target_user_id);
if (!$u) {
    echo '<div class="error" style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px; border:1px solid #fca5a5; font-weight:700; font-family:\'Cairo\', sans-serif;">خطأ: لم يتم العثور على الموظف المطلوب.</div>';
    return;
}

// Security: Employees can only access their own profile
if ($target_user_id !== $current_user->ID && !($is_admin || $is_sys_admin || $is_hr)) {
    $target_user_id = $current_user->ID;
    $u = $current_user;
}

// Arabic role translation map
$role_map = array(
    'administrator' => 'الإدارة المركزية (المطور)',
    'sm_system_admin' => 'مدير النظام التقني',
    'sm_principal' => 'مدير المدرسة',
    'sm_supervisor' => 'مشرف تربوي',
    'sm_coordinator' => 'منسق مادة',
    'sm_teacher' => 'معلم',
    'sm_discipline_supervisor' => 'مشرف سلوك / انضباط',
    'sm_activities_supervisor' => 'مشرف أنشطة',
    'sm_transportation_supervisor' => 'مشرف نقل ومواصلات',
    'sm_bus_supervisor' => 'مشرف حافلة',
    'sm_clinic' => 'العيادة المدرسية',
    'sm_hr' => 'الموارد البشرية (HR)',
    'sm_student' => 'طالب',
    'sm_parent' => 'ولي أمر'
);

// Form submission handler for saving/synchronizing account and profile details
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['eess_save_profile_action'])) {
    // CSRF Check
    if (!isset($_POST['eess_profile_nonce']) || !wp_verify_nonce($_POST['eess_profile_nonce'], 'eess_save_profile')) {
        wp_die('عذراً، انتهت صلاحية الجلسة. يرجى المحاولة مجدداً.');
    }

    // Permission Check: only Admin, HR, or the employee himself can save personal info
    if ($target_user_id === $current_user->ID || $is_admin || $is_sys_admin || $is_hr) {
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name  = sanitize_text_field($_POST['last_name'] ?? '');
        $email      = sanitize_email($_POST['email'] ?? '');
        $phone      = sanitize_text_field($_POST['phone'] ?? '');

        // Update core user record
        $user_data = array(
            'ID' => $target_user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
        );
        if (!empty($email) && $email !== $u->user_email) {
            $user_data['user_email'] = $email;
        }
        wp_update_user($user_data);

        // Update user meta
        update_user_meta($target_user_id, 'first_name', $first_name);
        update_user_meta($target_user_id, 'last_name', $last_name);
        update_user_meta($target_user_id, 'sm_phone', $phone);

        // HR/Admin only fields (Employment info, position, etc.)
        if ($is_admin || $is_sys_admin || $is_hr) {
            $emp_num    = sanitize_text_field($_POST['employee_number'] ?? '');
            $school     = sanitize_text_field($_POST['school_name'] ?? '');
            $dept       = sanitize_text_field($_POST['department'] ?? '');
            $spec       = sanitize_text_field($_POST['specialization'] ?? '');
            $emp_date   = sanitize_text_field($_POST['employment_date'] ?? '');
            $emp_status = sanitize_text_field($_POST['employment_status'] ?? 'active');

            update_user_meta($target_user_id, 'eess_employee_number', $emp_num);
            update_user_meta($target_user_id, 'eess_school_name', $school);
            update_user_meta($target_user_id, 'eess_department', $dept);
            update_user_meta($target_user_id, 'sm_specialization', $spec);
            update_user_meta($target_user_id, 'eess_hr_employment_date', $emp_date);
            update_user_meta($target_user_id, 'eess_hr_employment_status', $emp_status);
        }

        // Log action in activity timeline
        $timeline = get_user_meta($target_user_id, 'eess_hr_activity_timeline', true) ?: array();
        if (!is_array($timeline)) $timeline = array();
        array_unshift($timeline, array(
            'date' => current_time('Y-m-d H:i:s'),
            'action' => 'تحديث الملف الشخصي',
            'actor' => $current_user->display_name,
            'details' => 'تم تحديث بيانات الملف التعريفي والاتصال بنجاح.'
        ));
        update_user_meta($target_user_id, 'eess_hr_activity_timeline', $timeline);

        // Clean user cache
        clean_user_cache($target_user_id);
        wp_cache_flush();

        // Refresh user data reference
        $u = get_userdata($target_user_id);

        echo '<div style="background:#dcfce7; color:#15803d; padding:15px; border-radius:8px; border:1px solid #bbf7d0; font-weight:700; margin-bottom:20px; font-family:\'Cairo\', sans-serif;">✅ تم تحديث وتزامن بيانات الموظف بنجاح في قاعدة البيانات والأنظمة المرتبطة.</div>';
    }
}

// Fetch HR fields from metadata
$first_name = get_user_meta($target_user_id, 'first_name', true) ?: $u->first_name;
$last_name = get_user_meta($target_user_id, 'last_name', true) ?: $u->last_name;
$emp_num = get_user_meta($target_user_id, 'eess_employee_number', true) ?: '';
$school_name = get_user_meta($target_user_id, 'eess_school_name', true) ?: 'خدمات الأنظمة الإلكترونية التعليمية (EESS)';
$dept = get_user_meta($target_user_id, 'eess_department', true) ?: 'غير محدد';
$specialization = get_user_meta($target_user_id, 'sm_specialization', true) ?: 'غير محدد';
$employment_date = get_user_meta($target_user_id, 'eess_hr_employment_date', true) ?: '';
$employment_status = get_user_meta($target_user_id, 'eess_hr_employment_status', true) ?: 'active';
$phone = get_user_meta($target_user_id, 'sm_phone', true) ?: '';

// Load list metadata structures
$salary_records = get_user_meta($target_user_id, 'eess_hr_salary_records', true) ?: array();
if (!is_array($salary_records)) $salary_records = json_decode($salary_records, true) ?: array();

$disciplinary_records = get_user_meta($target_user_id, 'eess_hr_disciplinary_records', true) ?: array();
if (!is_array($disciplinary_records)) $disciplinary_records = json_decode($disciplinary_records, true) ?: array();

$warning_notices = get_user_meta($target_user_id, 'eess_hr_warning_notices', true) ?: array();
if (!is_array($warning_notices)) $warning_notices = json_decode($warning_notices, true) ?: array();

$admin_actions = get_user_meta($target_user_id, 'eess_hr_admin_actions', true) ?: array();
if (!is_array($admin_actions)) $admin_actions = json_decode($admin_actions, true) ?: array();

$hr_documents = get_user_meta($target_user_id, 'eess_hr_documents', true) ?: array();
if (!is_array($hr_documents)) $hr_documents = json_decode($hr_documents, true) ?: array();

$employment_history = get_user_meta($target_user_id, 'eess_hr_employment_history', true) ?: array();
if (!is_array($employment_history)) $employment_history = json_decode($employment_history, true) ?: array();

$evaluations = get_user_meta($target_user_id, 'eess_hr_evaluations', true) ?: array();
if (!is_array($evaluations)) $evaluations = json_decode($evaluations, true) ?: array();

$leaves = get_user_meta($target_user_id, 'eess_hr_leaves', true) ?: array();
if (!is_array($leaves)) $leaves = json_decode($leaves, true) ?: array();

$timeline = get_user_meta($target_user_id, 'eess_hr_activity_timeline', true) ?: array();
if (!is_array($timeline)) $timeline = json_decode($timeline, true) ?: array();
?>

<div class="sm-container" style="padding: 10px 0; font-family: 'Cairo', sans-serif !important; direction: rtl;">

    <!-- Main Workspace with Tabs in Left-Sidebar Vertical Format (RTL compliant) -->
    <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: var(--sm-shadow); padding: 25px; min-height: 500px; display: flex; gap: 25px; direction: rtl; align-items: flex-start;">

        <!-- Right side: Content Panels (flex: 1) -->
        <div style="flex: 1; min-width: 0;">
            <!-- Section 1: Personal Information -->
            <div id="wp-personal" class="wp-tab-content" style="display: block;">

                <!-- Employee Overview Top Header (Moved to first tab: Personal Information) -->
                <div style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: var(--sm-shadow);">
                    <div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">
                        <div style="text-align: center;">
                            <?php
                            $pending_photo = get_user_meta($target_user_id, 'eess_pending_profile_photo', true);
                            echo get_avatar($target_user_id, 110, '', '', array('style' => 'width: 110px; height: 110px; border-radius: 50% !important; border: 4px solid #cbd5e1; object-fit: cover; display: block; margin: 0 auto;'));
                            ?>
                            <form method="POST" enctype="multipart/form-data" action="" style="margin-top: 10px;">
                                <?php wp_nonce_field('eess_profile_photo_upload', 'eess_photo_nonce'); ?>
                                <label class="sm-btn sm-btn-outline" style="font-size: 11px; padding: 4px 10px; cursor: pointer; border-radius: 6px; display: inline-block; background: #fff; border-color: #cbd5e1; font-weight: bold;">
                                    📁 تغيير الصورة الشخصية
                                    <input type="file" name="profile_photo_upload" onchange="this.form.submit()" style="display: none;">
                                </label>
                            </form>
                            <?php if ($pending_photo): ?>
                                <div style="margin-top: 5px; font-size: 10px; color: #d97706; font-weight: bold; background: #fffbeb; border: 1px solid #fef3c7; padding: 4px; border-radius: 4px; text-align: center; max-width: 180px; margin-left: auto; margin-right: auto;">
                                    ⚠️ الصورة الجديدة قيد الاعتماد من قبل HR
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="flex: 1; min-width: 280px; text-align: right;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <h2 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 1.6rem;"><?php echo esc_html($u->display_name); ?></h2>
                                <span style="background: #334155; color: #f8fafc; padding: 2px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                    <?php echo $role_map[$u->roles[0]] ?? $u->roles[0]; ?>
                                </span>
                                <?php if ($employment_status === 'active'): ?>
                                    <span style="background: #dcfce7; color: #15803d; padding: 2px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #bbf7d0;">نشط بالخدمة</span>
                                <?php elseif ($employment_status === 'restricted'): ?>
                                    <span style="background: #fee2e2; color: #991b1b; padding: 2px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #fca5a5;">مقيد الدخول للمنصة</span>
                                <?php else: ?>
                                    <span style="background: #f1f5f9; color: #475569; padding: 2px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #cbd5e1;">غير نشط / إجازة</span>
                                <?php endif; ?>
                            </div>
                            <p style="margin: 6px 0; font-size: 0.85rem; color: #64748b; font-family: monospace;">@<?php echo esc_html($u->user_login); ?> | <?php echo esc_html($u->user_email); ?></p>

                            <div style="display: flex; gap: 15px; margin-top: 12px; flex-wrap: wrap; font-size: 12px;">
                                <span style="color: #475569;">
                                    <strong>رقم الموظف:</strong> <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;"><?php echo esc_html($emp_num ?: 'غير محدد'); ?></code>
                                </span>
                                <span style="color: #475569;">
                                    <strong>القسم:</strong> <?php echo esc_html($dept); ?>
                                </span>
                                <span style="color: #475569;">
                                    <strong>المادة/التخصص:</strong> <span style="color: #64748b; font-weight: 700;"><?php echo esc_html($specialization); ?></span>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">البيانات الشخصية وتفاصيل الاتصال</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">الاسم الأول:</span>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo esc_html($first_name ?: 'غير محدد'); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">اسم العائلة / اللقب:</span>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo esc_html($last_name ?: 'غير محدد'); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">الاسم الكامل للموظف:</span>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo esc_html($u->display_name); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">البريد الإلكتروني المعتمد:</span>
                    <strong style="font-size: 14px; color: #1e293b; font-family: monospace;"><?php echo esc_html($u->user_email); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">رقم الهاتف المتحرك:</span>
                    <strong style="font-size: 14px; color: #1e293b; font-family: monospace;"><?php echo esc_html($phone ?: 'غير محدد'); ?></strong>
                </div>
            </div>
        </div>

        <!-- Section 2: Employment Information -->
        <div id="wp-employment" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">💼 بيانات المباشرة والتعيين الرسمي</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">تاريخ مباشرة العمل:</span>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo esc_html($employment_date ?: 'غير مسجل'); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">المؤسسة التعليمية التابع لها:</span>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo esc_html($school_name); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">نوع التوظيف:</span>
                    <strong style="font-size: 14px; color: #1e293b;">دائم / دوام كامل</strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">تاريخ انتهاء التعاقد المخطط:</span>
                    <strong style="font-size: 14px; color: #1e293b;">مستمر</strong>
                </div>
            </div>
        </div>

        <!-- Section 3: Position Details -->
        <div id="wp-position" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">⚙️ تفاصيل المنصب الحالي والمسؤوليات والمهام المسندة</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">المنصب الحالي في النظام:</span>
                    <strong style="font-size: 14px; color: var(--sm-primary-color);"><?php echo $role_map[$u->roles[0]] ?? $u->roles[0]; ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">الإدارة / القسم التنظيمي:</span>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo esc_html($dept); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">التخصص التدريسي أو الأكاديمي:</span>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo esc_html($specialization); ?></strong>
                </div>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 12px; display: block;">سلطة المباشرة:</span>
                    <strong style="font-size: 14px; color: #1e293b;">مدير المدرسة المباشر</strong>
                </div>
            </div>
        </div>

        <!-- Section 4: Salary Information -->
        <div id="wp-salaries" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">📊 معلومات الرواتب والتعويضات والبدلات</h4>
            <div class="sm-table-container">
                <table class="sm-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>الشهر / تاريخ الاستحقاق</th>
                            <th>الراتب الأساسي</th>
                            <th>بدل السكن</th>
                            <th>بدل الانتقال</th>
                            <th>خصومات واستقطاعات</th>
                            <th>الصافي المستلم</th>
                            <th>تفاصيل وملاحظات الصرف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($salary_records)): ?>
                            <tr><td colspan="7" style="text-align: center; color: #94a3b8; padding: 30px;">لا توجد أي سجلات مالية أو رواتب مسجلة لهذا الموظف حالياً.</td></tr>
                        <?php else: ?>
                            <?php foreach ($salary_records as $rec): ?>
                                <tr>
                                    <td style="font-weight: 800; color: #1e293b;"><?php echo esc_html($rec['date']); ?></td>
                                    <td><?php echo number_format($rec['basic'] ?? 0, 2); ?> د.إ</td>
                                    <td><?php echo number_format($rec['housing'] ?? 0, 2); ?> د.إ</td>
                                    <td><?php echo number_format($rec['transport'] ?? 0, 2); ?> د.إ</td>
                                    <td style="color: #dc2626; font-weight: bold;">-<?php echo number_format($rec['deductions'] ?? 0, 2); ?> د.إ</td>
                                    <td style="color: #16a34a; font-weight: 800; font-size: 14px;"><?php echo number_format($rec['net'] ?? 0, 2); ?> د.إ</td>
                                    <td style="font-style: italic; font-size: 12px; color: #64748b;"><?php echo esc_html($rec['notes'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 5: Disciplinary Records -->
        <div id="wp-disciplinary" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">⚠️ السجلات التأديبية، التنبيهات والإنذارات الرسمية</h4>
            <div style="margin-bottom: 30px;">
                <h5 style="margin: 0 0 10px 0; font-weight: bold; color: #475569;">⚠️ التنبيهات والإنذارات الرسمية</h5>
                <div class="sm-table-container">
                    <table class="sm-table" style="margin:0;">
                        <thead>
                            <tr>
                                <th>تاريخ الإصدار</th>
                                <th>نوع الإنذار / التنبيه</th>
                                <th>التفاصيل والمسببات</th>
                                <th>حالة الملف حالياً</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($warning_notices)): ?>
                                <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">السجل خالي من أي إنذارات أو تنبيهات رسمية.</td></tr>
                            <?php else: ?>
                                <?php foreach ($warning_notices as $warn): ?>
                                    <tr>
                                        <td><?php echo esc_html($warn['date']); ?></td>
                                        <td style="font-weight: 700; color: #dc2626;"><?php echo esc_html($warn['subject']); ?></td>
                                        <td style="font-size: 12px;"><?php echo esc_html($warn['details']); ?></td>
                                        <td>
                                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">
                                                <?php echo esc_html($warn['status'] ?? 'نشط'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h5 style="margin: 0 0 10px 0; font-weight: bold; color: #475569;">🛑 محاضر مجالس الانضباط والجزاءات</h5>
                <div class="sm-table-container">
                    <table class="sm-table" style="margin:0;">
                        <thead>
                            <tr>
                                <th>تاريخ المحضر</th>
                                <th>المخالفة أو الواقعة</th>
                                <th>الإجراء الجزائي المعتمد</th>
                                <th>المسؤول أو جهة القرار</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($disciplinary_records)): ?>
                                <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">السجل خالي من أي محاضر انضباطية.</td></tr>
                            <?php else: ?>
                                <?php foreach ($disciplinary_records as $disc): ?>
                                    <tr>
                                        <td><?php echo esc_html($disc['date']); ?></td>
                                        <td><?php echo esc_html($disc['incident']); ?></td>
                                        <td style="color: #dc2626; font-weight: bold;"><?php echo esc_html($disc['action']); ?></td>
                                        <td><?php echo esc_html($disc['supervisor']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 6: Performance Evaluations -->
        <div id="wp-evaluations" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">📈 تقييم الأداء والتقارير والتقييمات السنوية</h4>
            <div class="sm-table-container">
                <table class="sm-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>فترة التقييم</th>
                            <th>الدرجة النهائية (%)</th>
                            <th>التقدير العام</th>
                            <th>توصيات وملاحظات رئيس المباشرة</th>
                            <th>المقيّم المعتمد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($evaluations)): ?>
                            <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 30px;">لا توجد تقييمات أداء مسجلة لهذا الموظف حتى الآن.</td></tr>
                        <?php else: ?>
                            <?php foreach ($evaluations as $eval): ?>
                                <tr>
                                    <td style="font-weight: 800;"><?php echo esc_html($eval['period'] ?? $eval['date'] ?? 'غير محدد'); ?></td>
                                    <td style="font-weight: bold; font-family: monospace; font-size: 14px; color: var(--sm-primary-color);"><?php echo esc_html($eval['score'] ?? '0'); ?>%</td>
                                    <td>
                                        <span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                            <?php echo esc_html($eval['grade'] ?? 'غير محدد'); ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 12px; color: #475569;"><?php echo esc_html($eval['notes'] ?? $eval['comments'] ?? ''); ?></td>
                                    <td><?php echo esc_html($eval['evaluator'] ?? 'الإدارة'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 7: Official Documents -->
        <div id="wp-docs" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">📄 الوثائق الرسمية، الهويات، المؤهلات والشهادات المؤرشفة</h4>
            <div class="sm-table-container">
                <table class="sm-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>تاريخ المرفق</th>
                            <th>اسم وثيقة الثبوتية</th>
                            <th>رابط المستند المؤرشف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hr_documents)): ?>
                            <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 30px;">لم يتم رفع أي مستندات رسمية أو ثبوتية للموظف.</td></tr>
                        <?php else: ?>
                            <?php foreach ($hr_documents as $doc): ?>
                                <tr>
                                    <td><?php echo esc_html($doc['date']); ?></td>
                                    <td style="font-weight: 700;"><?php echo esc_html($doc['name']); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url($doc['file_url']); ?>" target="_blank" class="sm-btn" style="padding: 4px 12px; height: 28px; width: auto; font-size: 11px; background: #000; color: white !important; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                            📥 معاينة وتحميل الملف
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 8: Employment History -->
        <div id="wp-history" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">⏳ السجل التاريخي والترقيات والخبرات السابقة</h4>
            <div class="sm-table-container">
                <table class="sm-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>تاريخ وتوقيت التحديث</th>
                            <th>المسمى الوظيفي / دور العمل</th>
                            <th>المدرسة / المؤسسة المشغلة</th>
                            <th>ملاحظات وتفاصيل الترقية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($employment_history)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">لا توجد ترقيات أو سجل خبرة تاريخي مسجل مسبقاً.</td></tr>
                        <?php else: ?>
                            <?php foreach ($employment_history as $hist): ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php echo esc_html($hist['date']); ?></td>
                                    <td style="font-weight: 700; color: var(--sm-primary-color);"><?php echo esc_html($hist['role']); ?></td>
                                    <td><?php echo esc_html($hist['organization']); ?></td>
                                    <td style="font-size: 12px; color: #475569;"><?php echo esc_html($hist['notes']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 9: Leave Records -->
        <div id="wp-leaves" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">📅 سجل الإجازات الرسمية، العارضة والمغادرات اليومية</h4>
            <div class="sm-table-container">
                <table class="sm-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>من تاريخ</th>
                            <th>إلى تاريخ</th>
                            <th>نوع الإجازة / الطلب</th>
                            <th>عدد الأيام الفعلي</th>
                            <th>ملاحظات معتمدة</th>
                            <th>حالة الطلب والموافقة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaves)): ?>
                            <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px;">لم يتم رصد أو تسجيل أي إجازات أو طلبات مغادرة حتى الآن.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leaves as $lv): ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php echo esc_html($lv['start_date']); ?></td>
                                    <td style="font-weight: bold;"><?php echo esc_html($lv['end_date']); ?></td>
                                    <td><?php echo esc_html($lv['type']); ?></td>
                                    <td><?php echo esc_html($lv['days']); ?> أيام</td>
                                    <td style="font-size:12px; color:#475569;"><?php echo esc_html($lv['notes'] ?? ''); ?></td>
                                    <td>
                                        <span style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:4px; font-weight:bold; font-size:11px;">
                                            <?php echo esc_html($lv['status'] ?? 'موافق عليه'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 10: Administrative Notes -->
        <div id="wp-notes" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">📝 الملاحظات الإدارية المعتمدة والتوصيات المباشرة</h4>
            <div class="sm-table-container">
                <table class="sm-table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>القرار أو الإشعار الإداري</th>
                            <th>تفاصيل وملاحظات القسم المسؤول</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admin_actions)): ?>
                            <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 30px;">لا توجد أي ملاحظات أو توجيهات إدارية مرصودة في سجل الملف حالياً.</td></tr>
                        <?php else: ?>
                            <?php foreach ($admin_actions as $act): ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php echo esc_html($act['date']); ?></td>
                                    <td style="font-weight: 700; color: #1e293b;"><?php echo esc_html($act['action']); ?></td>
                                    <td style="font-size: 12px; color: #475569;"><?php echo esc_html($act['notes']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 11: Activity Timeline -->
        <div id="wp-timeline" class="wp-tab-content" style="display: none;">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">سجل الأنشطة والعمليات التاريخية</h4>
            <div style="padding: 10px 15px;">
                <?php if (empty($timeline)): ?>
                    <p style="color: #94a3b8; text-align: center; padding: 20px;">لا يوجد سجل أنشطة أو عمليات تاريخية مسجلة في هذا الملف بعد.</p>
                <?php else: ?>
                    <div style="position: relative; border-right: 2px solid #cbd5e1; padding-right: 20px; margin-right: 10px;">
                        <?php foreach ($timeline as $tl): ?>
                            <div style="position: relative; margin-bottom: 20px;">
                                <!-- Dot indicator -->
                                <span style="position: absolute; right: -26px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--sm-primary-color); border: 2px solid white;"></span>
                                <div style="font-size: 11px; color: #64748b; font-weight: bold;"><?php echo esc_html($tl['date']); ?></div>
                                <div style="font-weight: bold; color: #1e293b; font-size: 13px; margin: 2px 0;"><?php echo esc_html($tl['action']); ?> <span style="font-weight:normal; font-size:11px; color:#64748b;">(بواسطة: <?php echo esc_html($tl['actor'] ?? 'النظام'); ?>)</span></div>
                                <div style="font-size: 12px; color: #475569;"><?php echo esc_html($tl['details']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        </div>

        <!-- Left side: Vertical Sidebar Navigation (width: 250px) -->
        <div style="width: 250px; flex-shrink: 0; border-right: 1px solid #cbd5e0; padding-right: 15px; display: flex; flex-direction: column; gap: 8px;">
            <button onclick="switchEmployeeProfileTab('wp-personal', this)" class="sm-tab-btn sm-active" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #334155; color: white;">البيانات الشخصية</button>
            <button onclick="switchEmployeeProfileTab('wp-employment', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">بيانات المباشرة</button>
            <button onclick="switchEmployeeProfileTab('wp-position', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">تفاصيل المنصب والمهام</button>
            <button onclick="switchEmployeeProfileTab('wp-salaries', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">معلومات الرواتب</button>
            <button onclick="switchEmployeeProfileTab('wp-disciplinary', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">السجلات التأديبية</button>
            <button onclick="switchEmployeeProfileTab('wp-evaluations', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">تقييم الأداء</button>
            <button onclick="switchEmployeeProfileTab('wp-docs', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">الوثائق الرسمية</button>
            <button onclick="switchEmployeeProfileTab('wp-history', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">السجل المهني</button>
            <button onclick="switchEmployeeProfileTab('wp-leaves', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">سجل الإجازات</button>
            <button onclick="switchEmployeeProfileTab('wp-notes', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">الملاحظات الإدارية</button>
            <button onclick="switchEmployeeProfileTab('wp-timeline', this)" class="sm-tab-btn" style="text-align: right; width: 100%; border: none; font-size: 13px; font-weight: 700; padding: 10px 15px; border-radius: 6px; cursor: pointer; transition: 0.2s; background: #f8fafc; color: #475569;">سجل الأنشطة</button>
        </div>

    </div>

</div>

<?php include_once SM_PLUGIN_DIR . 'templates/partials/unified-user-modal.php'; ?>
<!-- Interactive Modal for Editing Employee Profile with Instant System Sync -->
<div id="eessProfileEditModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(2px);">
    <div style="background: #fff; width: 100%; max-width: 650px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">

        <!-- Header -->
        <div style="background: #1e293b; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; font-family: 'Cairo', sans-serif;">تعديل وتزامن معلومات الموظف</h3>
            <button type="button" onclick="eessCloseProfileEditModal()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Body with edit form -->
        <form method="POST" action="" style="padding: 20px; overflow-y: auto; flex: 1; font-family: 'Cairo', sans-serif;">
            <?php wp_nonce_field('eess_save_profile', 'eess_profile_nonce'); ?>
            <input type="hidden" name="eess_save_profile_action" value="1">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">الاسم الأول *</label>
                    <input type="text" name="first_name" value="<?php echo esc_attr($first_name); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">اسم العائلة *</label>
                    <input type="text" name="last_name" value="<?php echo esc_attr($last_name); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">البريد الإلكتروني المعتمد *</label>
                    <input type="email" name="email" value="<?php echo esc_attr($u->user_email); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">رقم الهاتف المتحرك *</label>
                    <input type="text" name="phone" value="<?php echo esc_attr($phone); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                </div>
            </div>

            <?php if ($is_admin || $is_sys_admin || $is_hr): ?>
                <div style="border-top: 1px dashed #cbd5e1; margin: 15px 0; padding-top: 15px;">
                    <h4 style="margin:0 0 15px 0; font-size:13px; font-weight:800; color:#1e293b;">🔒 حقول الموارد البشرية والتعيين (خاص بالإدارة / HR)</h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">رقم الموظف الوظيفي</label>
                            <input type="text" name="employee_number" value="<?php echo esc_attr($emp_num); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">القسم / الإدارة</label>
                            <input type="text" name="department" value="<?php echo esc_attr($dept); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">المادة أو التخصص الدراسي</label>
                            <input type="text" name="specialization" value="<?php echo esc_attr($specialization); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">المؤسسة أو المدرسة التابع لها</label>
                            <input type="text" name="school_name" value="<?php echo esc_attr($school_name); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">تاريخ مباشرة العمل</label>
                            <input type="date" name="employment_date" value="<?php echo esc_attr($employment_date); ?>" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo';">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 5px;">حالة التوظيف في الخدمة</label>
                            <select name="employment_status" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-family:'Cairo'; height:40px;">
                                <option value="active" <?php selected($employment_status, 'active'); ?>>نشط بالخدمة</option>
                                <option value="on_leave" <?php selected($employment_status, 'on_leave'); ?>>غير نشط / إجازة</option>
                                <option value="restricted" <?php selected($employment_status, 'restricted'); ?>>مقيد الدخول للمنصة</option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Footer Buttons -->
            <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #cbd5e1; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="eessCloseProfileEditModal()" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; padding:10px 20px; border-radius:8px; font-weight:700; cursor:pointer; font-family:'Cairo';">إلغاء</button>
                <button type="submit" style="background:#000; color:#white; border:none; padding:10px 25px; border-radius:8px; font-weight:700; cursor:pointer; color:white !important; font-family:'Cairo';">حفظ ومزامنة البيانات</button>
            </div>
        </form>

    </div>
</div>

<script>
// Tab Switching logic for 11 distinct sections inside Employee Profile
function switchEmployeeProfileTab(tabId, btn) {
    document.querySelectorAll('.wp-tab-content').forEach(el => el.style.display = 'none');
    const tabEl = document.getElementById(tabId);
    if (tabEl) tabEl.style.display = 'block';

    btn.parentElement.querySelectorAll('.sm-tab-btn').forEach(b => b.classList.remove('sm-active'));
    btn.classList.add('sm-active');
}

// Modal Toggle Utilities
function eessOpenProfileEditModal() {
    eessOpenUnifiedUserModal('edit_employee_profile', <?php echo $emp->ID; ?>);
}
function eessCloseProfileEditModal() {
    document.getElementById('eessProfileEditModal').style.display = 'none';
}
</script>
