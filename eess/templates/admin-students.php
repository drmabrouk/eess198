<?php if (!defined('ABSPATH')) exit; ?>
<?php
$is_admin = current_user_can('شؤون_الطلاب');
$import_results = get_transient('sm_import_results_' . get_current_user_id());
if ($import_results) {
    delete_transient('sm_import_results_' . get_current_user_id());
}
?>
<div class="sm-content-wrapper" dir="rtl" style="font-family: 'Cairo', sans-serif !important;">
    <!-- Standardized header is rendered globally in public-admin-panel.php, so we omit any local headers here -->

    <?php if ($import_results): ?>
        <div style="background: #fff; border-radius: 12px; border: 1px solid var(--sm-border-color); margin-bottom: 30px; overflow: hidden; box-shadow: var(--sm-shadow);">
            <div style="background: var(--sm-bg-light); padding: 15px 25px; border-bottom: 1px solid var(--sm-border-color); display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin:0; color: var(--sm-dark-color); font-weight: 800;">تقرير استيراد الطلاب الأخير</h4>
                <span style="font-size: 12px; color: #718096;">إجمالي السجلات المعالجة: <?php echo $import_results['total']; ?></span>
            </div>
            <div style="padding: 25px;">
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 25px;">
                    <div style="background: #f0fff4; padding: 15px; border-radius: 8px; border: 1px solid #c6f6d5; text-align: center;">
                        <div style="font-size: 20px; font-weight: 800; color: #2f855a;"><?php echo $import_results['success'] - ($import_results['duplicate'] ?? 0); ?></div>
                        <div style="font-size: 11px; color: #38a169;">سجلات جديدة</div>
                    </div>
                    <div style="background: #e6fffa; padding: 15px; border-radius: 8px; border: 1px solid #b2f5ea; text-align: center;">
                        <div style="font-size: 20px; font-weight: 800; color: #2c7a7b;"><?php echo $import_results['generated'] ?? 0; ?></div>
                        <div style="font-size: 11px; color: #319795;">أكواد تم توليدها</div>
                    </div>
                    <div style="background: #ebf8ff; padding: 15px; border-radius: 8px; border: 1px solid #bee3f8; text-align: center;">
                        <div style="font-size: 20px; font-weight: 800; color: #2b6cb0;"><?php echo $import_results['duplicate'] ?? 0; ?></div>
                        <div style="font-size: 11px; color: #3182ce;">سجلات مكررة</div>
                    </div>
                    <div style="background: #fffaf0; padding: 15px; border-radius: 8px; border: 1px solid #feebc8; text-align: center;">
                        <div style="font-size: 20px; font-weight: 800; color: #c05621;"><?php echo $import_results['warning']; ?></div>
                        <div style="font-size: 11px; color: #dd6b20;">تنبيهات</div>
                    </div>
                    <div style="background: #fff5f5; padding: 15px; border-radius: 8px; border: 1px solid #fed7d7; text-align: center;">
                        <div style="font-size: 20px; font-weight: 800; color: #c53030;"><?php echo $import_results['error']; ?></div>
                        <div style="font-size: 11px; color: #e53e3e;">أخطاء</div>
                    </div>
                </div>

                <?php if (!empty($import_results['details'])): ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; max-height: 250px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: right;">
                            <thead>
                                <tr style="background: #edf2f7; position: sticky; top: 0;">
                                    <th style="padding: 10px 15px; border-bottom: 1px solid #cbd5e0; width: 80px;">النوع</th>
                                    <th style="padding: 10px 15px; border-bottom: 1px solid #cbd5e0;">التفاصيل والسبب</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($import_results['details'] as $detail): ?>
                                    <tr>
                                        <td style="padding: 10px 15px; border-bottom: 1px solid #e2e8f0;">
                                            <?php if ($detail['type'] == 'error'): ?>
                                                <span style="color: #e53e3e; font-weight: 700;">خطأ</span>
                                            <?php elseif ($detail['type'] == 'info'): ?>
                                                <span style="color: #3182ce; font-weight: 700;">تكرار</span>
                                            <?php else: ?>
                                                <span style="color: #dd6b20; font-weight: 700;">تنبيه</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 10px 15px; border-bottom: 1px solid #e2e8f0; color: #4a5568;"><?php echo esc_html($detail['msg']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div style="background: white; padding: 25px; border: 1px solid var(--sm-border-color); border-radius: var(--sm-radius); margin-bottom: 20px; box-shadow: var(--sm-shadow);">
        <form method="get" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
            <input type="hidden" name="sm_tab" value="students">

            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label" style="font-size:12px; font-weight:bold;">اسم الطالب:</label>
                <input type="text" name="student_search" class="sm-input" value="<?php echo esc_attr(isset($_GET['student_search']) ? $_GET['student_search'] : ''); ?>" placeholder="بحث بالاسم أو الكود..." style="height:36px; font-size:12px;">
            </div>
            
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label" style="font-size:12px; font-weight:bold;">الصف:</label>
                <select name="class_filter" class="sm-select" style="height:36px; font-size:12px; padding:0 10px;">
                    <option value="">كل الصفوف</option>
                    <?php 
                    global $wpdb;
                    $academic = SM_Settings::get_academic_structure();
                    foreach ($academic['active_grades'] as $grade_num) {
                        $grade_label = 'الصف ' . $grade_num;
                        echo '<option value="' . esc_attr($grade_label) . '" ' . selected(isset($_GET['class_filter']) && $_GET['class_filter'] == $grade_label, true, false) . '>' . esc_html($grade_label) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label" style="font-size:12px; font-weight:bold;">الشعبة:</label>
                <input type="text" name="section_filter" class="sm-input" value="<?php echo esc_attr(isset($_GET['section_filter']) ? $_GET['section_filter'] : ''); ?>" placeholder="مثال: أ" list="existing-sections" style="height:36px; font-size:12px;">
                <datalist id="existing-sections">
                    <?php
                    $sections = $wpdb->get_col("SELECT DISTINCT section FROM {$wpdb->prefix}sm_students WHERE section != '' ORDER BY section ASC");
                    foreach ($sections as $sec) echo "<option value='".esc_attr($sec)."'>";
                    ?>
                </datalist>
            </div>

            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label" style="font-size:12px; font-weight:bold;">المعلم المربّي:</label>
                <select name="teacher_filter" class="sm-select" style="height:36px; font-size:12px; padding:0 10px;">
                    <option value="">كل المعلمين</option>
                    <?php
                    $teachers = get_users(array('role' => 'sm_teacher'));
                    foreach ($teachers as $t) {
                        echo '<option value="' . $t->ID . '" ' . selected(isset($_GET['teacher_filter']) && $_GET['teacher_filter'] == $t->ID, true, false) . '>' . esc_html($t->display_name) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="sm-btn" style="height: 36px; font-size:12px; padding: 0 20px;">تصفية</button>
        </form>
    </div>

    <!-- Chunked File Upload Progress Form -->
    <div id="csv-import-form" style="display:none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: var(--sm-shadow);">
        <h4 style="margin-top:0; color:var(--sm-dark-color); font-weight: 800;">استيراد ذكي لملف الطلاب الشامل</h4>
        <p style="font-size:12px; color:#718096; line-height:1.6; margin-bottom:15px;">
            يرجى اختيار ملف الطلاب الخاص بك بصيغة CSV. يدعم الاستيراد ذو الأحجام الكبيرة بنظام الدفعات (Chunking) لضمان عدم توقف الخادم أو حدوث مهلات زمنية.
        </p>
        <div id="import-selection-area">
            <input type="file" id="csv-file-input" accept=".csv" class="sm-input" style="width: auto; display: inline-block; margin-bottom:15px; font-size:12px; height:36px;">
            <button onclick="startChunkedUpload()" class="sm-btn" style="width: auto; height:36px; font-size:12px;">بدء الاستيراد المجدول</button>
        </div>
        <div id="import-progress-area" style="display:none; margin-top:15px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:12px; font-weight:700;">
                <span id="import-status-text">جاري تحليل ومعالجة ملف الاستيراد...</span>
                <span id="import-percentage">0%</span>
            </div>
            <div style="background:#edf2f7; border-radius:50px; height:12px; overflow:hidden;">
                <div id="import-progress-bar" style="background:var(--sm-primary-color); width:0%; height:100%; transition:0.3s;"></div>
            </div>
        </div>
    </div>

    <!-- Dynamic Bulk Actions Toolbar (initially hidden, shows ONLY when checkboxes are checked) -->
    <div id="student-bulk-actions-toolbar" style="display: none; gap: 10px; margin-bottom: 15px; align-items: center; background: #f8fafc; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <span style="font-size: 12px; font-weight: 700; color: #4a5568;">الإجراءات الجماعية للطلاب المحددين:</span>
        <button onclick="bulkDeleteSelected()" class="sm-btn" style="background: #e53e3e; font-size: 11px; padding: 5px 15px; width: auto; height: 28px;">حذف المحدد</button>
    </div>

    <div class="sm-table-container">
        <table class="sm-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;"><input type="checkbox" onclick="toggleAllStudents(this)"></th>
                    <th>الكود الأكاديمي</th>
                    <th>اسم الطالب الكامل</th>
                    <th>الصف الدراسي</th>
                    <th>المعلم المربّي</th>
                    <th>النقاط</th>
                    <th style="text-align: left;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--sm-text-gray); padding: 40px;">لا يوجد طلاب يطابقون شروط البحث حالياً.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr id="stu-row-<?php echo $student->id; ?>">
                            <td style="text-align: center;"><input type="checkbox" class="student-checkbox" value="<?php echo $student->id; ?>" onchange="updateStudentBulkToolbar()"></td>
                            <td style="font-family: 'Rubik', sans-serif; font-weight: 700; color: var(--sm-primary-color);"><?php echo esc_html($student->student_code); ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <?php if ($student->photo_url): ?>
                                        <img src="<?php echo esc_url($student->photo_url); ?>" style="width: 40px; height: 40px; border-radius: 50% !important; object-fit: cover; border: 2px solid var(--sm-border-color);">
                                    <?php else: ?>
                                        <div style="background:#f1f5f9; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#cbd5e1; border:1px solid #e2e8f0;">
                                            <span class="dashicons dashicons-admin-users" style="font-size:20px; width:20px; height:20px;"></span>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight: 800; font-size:13px;"><?php echo esc_html($student->name); ?></div>
                                        <div style="font-size: 11px; color:#718096;"><?php echo esc_html($student->parent_email); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; font-size:13px;"><?php echo esc_html(SM_Settings::format_grade_name($student->class_name, $student->section)); ?></div>
                                <?php if (!empty($student->nationality)): ?>
                                    <div style="font-size: 10px; color: #a0aec0;"><?php echo esc_html($student->nationality); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $teacher = $student->teacher_id ? get_userdata($student->teacher_id) : null;
                                echo $teacher ? esc_html($teacher->display_name) : '<span style="color:#cbd5e1; font-style:italic;">غير معيّن</span>';
                                ?>
                            </td>
                            <td>
                                <span class="sm-badge <?php echo $student->behavior_points > 10 ? 'sm-badge-high' : ($student->behavior_points > 4 ? 'sm-badge-medium' : 'sm-badge-low'); ?>" style="font-size:11px; font-weight:800;">
                                    <?php echo intval($student->behavior_points); ?> نقطة
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button onclick="viewSmStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)" class="sm-btn" style="background:var(--sm-secondary-color); font-size:11px; padding: 4px 10px; width: auto; height: 28px;">الملف السلوكي</button>
                                    <?php if ($is_admin): ?>
                                        <button onclick="showStudentCreds('<?php echo esc_js($student->student_code); ?>', '<?php echo esc_js(get_user_meta($student->parent_user_id, 'sm_temp_pass', true)); ?>', '<?php echo esc_js($student->name); ?>', <?php echo $student->id; ?>)" class="sm-btn" style="background:#2d3748; font-size:11px; padding: 4px 10px; width: auto; height: 28px;">حساب الدخول</button>
                                        <button onclick='editSmStudent(<?php echo json_encode($student); ?>)' class="sm-btn" style="background:#edf2f7; color:#2d3748; font-size:11px; padding: 4px 10px; width: auto; height: 28px;">تعديل</button>
                                        <button onclick="confirmDeleteStudent(<?php echo $student->id; ?>, '<?php echo esc_js($student->name); ?>')" class="sm-btn" style="background:#e53e3e; font-size:11px; padding: 4px 10px; width: auto; height: 28px;">حذف</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ADD STUDENT MODAL -->
    <?php if ($is_admin): ?>
    <div id="add-single-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 650px;">
            <div class="sm-modal-header">
                <h3>إضافة طالب جديد لقاعدة البيانات</h3>
                <button class="sm-modal-close" onclick="document.getElementById('add-single-student-modal').style.display='none'">&times;</button>
            </div>
            <form id="add-student-form">
                <?php wp_nonce_field('sm_add_student', 'sm_nonce'); ?>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الكامل للطالب:</label>
                        <input type="text" name="name" class="sm-input" required placeholder="الاسم ثلاثي">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الصف الدراسي المعتمد:</label>
                        <select name="class" class="sm-select" required>
                            <option value="">-- اختر الصف --</option>
                            <?php 
                            foreach ($academic['active_grades'] as $grade_num) {
                                echo "<option value='الصف $grade_num'>الصف $grade_num</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الشعبة / الفصل:</label>
                        <input type="text" name="section" class="sm-input" required placeholder="مثال: أ أو ب" list="existing-sections">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">البريد الإلكتروني لولي الأمر:</label>
                        <input type="email" name="email" class="sm-input" placeholder="parent@example.com">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">رقم هاتف ولي الأمر:</label>
                        <input name="guardian_phone" type="text" class="sm-input" placeholder="05xxxxxxxx">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">جنسية الطالب:</label>
                        <input name="nationality" type="text" class="sm-input" placeholder="مثال: إماراتي">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">تاريخ التسجيل:</label>
                        <input name="registration_date" type="date" class="sm-input" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">ربط بحساب الطالب (اختياري):</label>
                        <select name="parent_user_id" class="sm-select">
                            <option value="">-- بلا ربط --</option>
                            <?php foreach (get_users(array('role' => 'sm_student')) as $p): ?>
                                <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="text-align:left; margin-top:25px;">
                    <button type="submit" class="sm-btn" style="width:200px; height:42px; font-weight:800; font-size:13px; font-family: 'Cairo', sans-serif !important;">تأكيد إضافة الطالب</button>
                </div>
            </form>
        </div>
    </div>

    <!-- REDESIGNED MULTI-STEP EDIT STUDENT PROFILE DIALOG (Cairo Font + Labels as Placeholders) -->
    <div id="edit-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 600px;">
            <div class="sm-modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin:0; font-weight:800; font-size: 15px;">تعديل بيانات الطالب</h3>
                <button class="sm-modal-close" onclick="document.getElementById('edit-student-modal').style.display='none'">&times;</button>
            </div>

            <form id="edit-student-form">
                <?php wp_nonce_field('sm_add_student', 'sm_nonce'); ?>
                <input type="hidden" name="student_id" id="edit_stu_id">

                <!-- Step Progress Bar -->
                <div class="eess-step-progress-bar" style="display:flex; justify-content:space-between; margin-bottom:20px; position:relative; font-family:'Cairo', sans-serif !important;">
                    <div class="eess-step-node active" id="edit-node-1" style="width:30px; height:30px; border-radius:50%; background:#000; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px; z-index:2; border: 2px solid #000; transition:0.3s;">1</div>
                    <div class="eess-step-node" id="edit-node-2" style="width:30px; height:30px; border-radius:50%; background:#fff; border:2px solid #cbd5e1; color:#64748b; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px; z-index:2; transition:0.3s;">2</div>
                    <div class="eess-step-node" id="edit-node-3" style="width:30px; height:30px; border-radius:50%; background:#fff; border:2px solid #cbd5e1; color:#64748b; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px; z-index:2; transition:0.3s;">3</div>
                </div>

                <!-- Step 1: Personal info -->
                <div id="edit-step-1" class="edit-wizard-step" style="display: block;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #edf2f7; font-family:'Cairo', sans-serif !important;">
                        <div style="grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 5px; color: var(--sm-primary-color); font-weight: 700; font-size:12px;">الخطوة 1: البيانات الشخصية والتعريفية</div>

                        <div class="sm-form-group" style="margin-bottom:0;">
                            <input type="text" name="name" id="edit_stu_name" class="sm-input" required placeholder="الاسم الكامل للطالب *" style="height:38px; font-size:12px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                        </div>
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <input type="text" name="nationality" id="edit_stu_nationality" class="sm-input" placeholder="جنسية الطالب" style="height:38px; font-size:12px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                        </div>
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <input type="date" name="registration_date" id="edit_stu_reg_date" class="sm-input" placeholder="تاريخ التسجيل" style="height:38px; font-size:12px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                        </div>
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <input type="text" name="student_code" id="edit_stu_code" class="sm-input" readonly placeholder="الرقم الأكاديمي (الكود)" style="height:38px; font-size:12px; background:#e2e8f0; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Academic info -->
                <div id="edit-step-2" class="edit-wizard-step" style="display: none;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #edf2f7; font-family:'Cairo', sans-serif !important;">
                        <div style="grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 5px; color: var(--sm-primary-color); font-weight: 700; font-size:12px;">الخطوة 2: الفصل والمرحلة الدراسية</div>

                        <div class="sm-form-group" style="margin-bottom:0;">
                            <select name="class_name" id="edit_stu_class" class="sm-select" required style="height:38px; font-size:12px; padding:0 10px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                                <option value="">-- اختر الصف الدراسي --</option>
                                <?php
                                foreach ($academic['active_grades'] as $grade_num) {
                                    echo "<option value='الصف $grade_num'>الصف $grade_num</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <input type="text" name="section" id="edit_stu_section" class="sm-input" required placeholder="الشعبة / الفصل *" list="existing-sections" style="height:38px; font-size:12px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Guardian & Account Info -->
                <div id="edit-step-3" class="edit-wizard-step" style="display: none;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; background:#f8fafc; padding:20px; border-radius:12px; border:1px solid #edf2f7; font-family:'Cairo', sans-serif !important;">
                        <div style="grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 5px; color: var(--sm-primary-color); font-weight: 700; font-size:12px;">الخطوة 3: معلومات التواصل وربط الحساب</div>

                        <div class="sm-form-group" style="margin-bottom:0;">
                            <input type="email" name="parent_email" id="edit_stu_email" class="sm-input" placeholder="البريد الإلكتروني لولي الأمر" style="height:38px; font-size:12px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                        </div>
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <input type="text" name="guardian_phone" id="edit_stu_phone" class="sm-input" placeholder="رقم هاتف ولي الأمر" style="height:38px; font-size:12px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                        </div>
                        <div class="sm-form-group" style="grid-column: span 2; margin-bottom:0;">
                            <select name="parent_user_id" id="edit_stu_parent_user" class="sm-select" style="height:38px; font-size:12px; padding:0 10px; font-family:'Cairo', sans-serif !important; border-radius: 8px;">
                                <option value="">-- ربط بحساب الطالب (اختياري) --</option>
                                <?php foreach (get_users(array('role' => 'sm_student')) as $p): ?>
                                    <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->display_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Wizard Actions Footer -->
                <div style="display:flex; gap:12px; margin-top:20px; justify-content: flex-end; font-family:'Cairo', sans-serif !important;">
                    <button type="button" id="edit-prev-btn" onclick="goEditStep(prevStepVal())" class="sm-btn" style="background:#cbd5e0; color:#2d3748 !important; width:100px; display:none; height: 36px; font-size:11px; border-radius: 6px;">السابق</button>
                    <button type="button" id="edit-next-btn" onclick="goEditStep(nextStepVal())" class="sm-btn" style="background:#000; color:#fff !important; width:100px; height: 36px; font-size:11px; border-radius: 6px;">التالي</button>
                    <button type="submit" id="edit-submit-btn" class="sm-btn" style="width:140px; height: 36px; font-size:11px; display:none; background:#8b1e1e; border-radius: 6px;">تحديث البيانات الآن</button>
                    <button type="button" onclick="document.getElementById('edit-student-modal').style.display='none'" class="sm-btn sm-btn-outline" style="width:100px; height:36px; font-size:11px; border-radius: 6px;">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- VIEW STUDENT RECORD MODAL -->
    <div id="view-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 900px; background: white;">
            <div class="sm-modal-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:20px;">
                <h3 style="margin:0; font-weight:800; font-size: 15px;">الملف السلوكي والتحليلي التفصيلي للطالب</h3>
                <div style="display:flex; gap:10px;">
                    <button id="print-full-record-btn" class="sm-btn" style="background:#27ae60; width:auto; font-size:11px; height:28px;">🖨️ طباعة الملف بالكامل</button>
                    <button class="sm-modal-close" onclick="document.getElementById('view-student-modal').style.display='none'" style="width:28px; height:28px; display:flex; align-items:center; justify-content:center;">&times;</button>
                </div>
            </div>
            <div class="sm-modal-body" id="stu_details_content" style="max-height: 70vh; overflow-y: auto;">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- DELETE STUDENT MODAL -->
    <div id="delete-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 450px;">
            <div class="sm-modal-header">
                <h3 style="color:#e53e3e;">تأكيد حذف الطالب نهائياً</h3>
                <button class="sm-modal-close" onclick="document.getElementById('delete-student-modal').style.display='none'">&times;</button>
            </div>
            <form id="delete-student-form">
                <p id="delete-confirm-msg" style="font-size:13px; color:#4a5568; line-height:1.6;"></p>
                <div style="background:#fff5f5; border:1px solid #fed7d7; padding:12px; border-radius:8px; font-size:11px; color:#c53030; margin-bottom:20px;">
                    ⚠️ تحذير: هذا الإجراء سيقوم بمسح كافة مخالفات الطالب، سجل الحضور، السجل الطبي، والدرجات نهائياً من قاعدة البيانات، ولا يمكن التراجع عنه.
                </div>
                <input type="hidden" id="confirm_delete_stu_id">
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="submit" class="sm-btn" style="background:#e53e3e; width:auto; height:36px; padding:0 20px; font-size:12px;">نعم، حذف الطالب الآن</button>
                    <button type="button" onclick="document.getElementById('delete-student-modal').style.display='none'" class="sm-btn sm-btn-outline" style="width:auto; height:36px; padding:0 15px; font-size:12px;">تراجع</button>
                </div>
            </form>
        </div>
    </div>

    <!-- STUDENT CREDENTIALS MODAL -->
    <div id="student-creds-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 450px;">
            <div class="sm-modal-header">
                <h3>بيانات الدخول الأكاديمية للطالب</h3>
                <button class="sm-modal-close" onclick="document.getElementById('student-creds-modal').style.display='none'">&times;</button>
            </div>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:20px; border-radius:12px; margin-bottom:20px; line-height:1.8;">
                <div style="font-weight:700; font-size:14px; color:var(--sm-primary-color); border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:15px;" id="cred-stu-name"></div>
                <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:10px;">
                    <span style="color:#718096;">اسم المستخدم (كود الطالب):</span>
                    <strong style="font-family:monospace;" id="cred-username"></strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:10px;">
                    <span style="color:#718096;">كلمة المرور الافتراضية:</span>
                    <strong style="font-family:monospace;" id="cred-password"></strong>
                </div>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <a id="cred-download-link" href="#" target="_blank" class="sm-btn" style="background:#3182ce; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; width:auto; height:36px; padding:0 20px; font-size:12px;">📥 تحميل بطاقة الدخول (PDF)</a>
                <button onclick="document.getElementById('student-creds-modal').style.display='none'" class="sm-btn sm-btn-outline" style="width:auto; height:36px; padding:0 15px; font-size:12px;">إغلاق</button>
            </div>
        </div>
    </div>

    <script>
    let currentEditStep = 1;
    function goEditStep(stepNum) {
        currentEditStep = stepNum;
        document.querySelectorAll('.edit-wizard-step').forEach(p => p.style.display = 'none');
        document.getElementById('edit-step-' + stepNum).style.display = 'block';

        // Update nodes
        for (let i = 1; i <= 3; i++) {
            const node = document.getElementById('edit-node-' + i);
            if (node) {
                if (i === stepNum) {
                    node.style.background = '#000';
                    node.style.color = '#fff';
                    node.style.borderColor = '#000';
                } else if (i < stepNum) {
                    node.style.background = '#15803d';
                    node.style.color = '#fff';
                    node.style.borderColor = '#15803d';
                } else {
                    node.style.background = '#fff';
                    node.style.color = '#64748b';
                    node.style.borderColor = '#cbd5e1';
                }
            }
        }

        // Toggle button visibilities
        document.getElementById('edit-prev-btn').style.display = stepNum > 1 ? 'inline-flex' : 'none';
        document.getElementById('edit-next-btn').style.display = stepNum < 3 ? 'inline-flex' : 'none';
        document.getElementById('edit-submit-btn').style.display = stepNum === 3 ? 'inline-flex' : 'none';
    }
    function prevStepVal() { return currentEditStep - 1; }
    function nextStepVal() { return currentEditStep + 1; }

    function updateStudentBulkToolbar() {
        const selected = document.querySelectorAll('.student-checkbox:checked').length;
        const toolbar = document.getElementById('student-bulk-actions-toolbar');
        if (toolbar) {
            toolbar.style.display = selected > 0 ? 'flex' : 'none';
        }
    }

    // Chunked File Upload Progress Form
    let chunkedFile, chunkedSize, chunkedId, chunkedTotalParts, chunkedCurrentPart;
    const CHUNK_SIZE = 100 * 1024; // 100kb chunks

    window.startChunkedUpload = function() {
        const fileInput = document.getElementById('csv-file-input');
        if (fileInput.files.length === 0) {
            alert('يرجى تحديد ملف CSV أولاً.');
            return;
        }

        chunkedFile = fileInput.files[0];
        chunkedSize = chunkedFile.size;
        chunkedTotalParts = Math.ceil(chunkedSize / CHUNK_SIZE);
        chunkedCurrentPart = 0;

        document.getElementById('import-selection-area').style.display = 'none';
        document.getElementById('import-progress-area').style.display = 'block';
        updateImportProgress('جاري رفع وتحليل ملف البيانات...', 0);

        // Upload First Chunk
        uploadNextChunk();
    };

    function uploadNextChunk() {
        const start = chunkedCurrentPart * CHUNK_SIZE;
        const end = Math.min(start + CHUNK_SIZE, chunkedSize);
        const chunk = chunkedFile.slice(start, end);

        const formData = new FormData();
        formData.append('action', 'sm_upload_import_csv');
        formData.append('csv_file', chunk, chunkedFile.name);
        formData.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Succeeded, now start processing
                processImportChunk(res.data.file_path, 0);
            } else {
                alert('فشل رفع الملف: ' + res.data);
                resetImportUI();
            }
        });
    }

    function processImportChunk(filePath, offset) {
        const formData = new FormData();
        formData.append('action', 'sm_process_import_chunk');
        formData.append('file_path', filePath);
        formData.append('offset', offset);
        formData.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const finished = res.data.finished;
                const processed = res.data.total_so_far;

                if (finished) {
                    updateImportProgress('تم الانتهاء من استيراد كافة البيانات بنجاح!', 100);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    const pct = Math.min(99, Math.round((offset / chunkedTotalParts) * 100)); // approximate progress
                    updateImportProgress(`جاري معالجة السجلات... تم معالجة ${processed} طالب`, pct);
                    processImportChunk(filePath, offset + res.data.processed);
                }
            } else {
                alert('خطأ أثناء المعالجة: ' + res.data);
                resetImportUI();
            }
        });
    }

    function updateImportProgress(text, pct) {
        document.getElementById('import-status-text').innerText = text;
        document.getElementById('import-percentage').innerText = pct + '%';
        document.getElementById('import-progress-bar').style.width = pct + '%';
    }

    function resetImportUI() {
        document.getElementById('import-selection-area').style.display = 'block';
        document.getElementById('import-progress-area').style.display = 'none';
    }

    (function() {
        // Show Credentials
        window.showStudentCreds = function(user, pass, name, id) {
            document.getElementById('cred-username').innerText = user;
            document.getElementById('cred-password').innerText = pass;
            document.getElementById('cred-stu-name').innerText = name;
            document.getElementById('cred-download-link').href = '<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=student_credentials_card&student_id='); ?>' + id;
            document.getElementById('student-creds-modal').style.display = 'flex';
        };

        // Handle View Record
        window.viewSmStudent = function(student) {
            const modal = document.getElementById('view-student-modal');
            const content = document.getElementById('stu_details_content');
            const printBtn = document.getElementById('print-full-record-btn');
            if (!modal || !content) return;
            
            content.innerHTML = '<div style="text-align:center; padding:50px;"><p style="font-weight:700; color:#718096;">جاري جلب الملف الانضباطي وتنسيقه...</p></div>';
            modal.style.display = 'flex';

            printBtn.onclick = function() {
                window.open('<?php echo admin_url('admin-ajax.php'); ?>?action=sm_print&print_type=disciplinary_report&student_id=' + student.id, '_blank');
            };

            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=sm_print&print_type=disciplinary_report&student_id=' + student.id)
                .then(r => r.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    doc.querySelectorAll('.no-print').forEach(el => el.remove());
                    content.innerHTML = doc.body.innerHTML;
                });
        };

        // Handle Add Student AJAX
        const addForm = document.getElementById('add-student-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_add_student_ajax');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تمت إضافة الطالب بنجاح');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        smShowNotification('خطأ: ' + res.data, true);
                    }
                })
                .catch(err => {
                    smShowNotification('حدث خطأ أثناء الاتصال بالخادم', true);
                });
            });
        }

        // Handle Edit Student AJAX
        const editForm = document.getElementById('edit-student-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_update_student_ajax');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تم تحديث بيانات الطالب');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        smShowNotification('خطأ: ' + res.data, true);
                    }
                })
                .catch(err => {
                    smShowNotification('حدث خطأ أثناء الاتصال بالخادم', true);
                });
            });
        }

        // Handle Delete
        window.confirmDeleteStudent = function(id, name) {
            document.getElementById('confirm_delete_stu_id').value = id;
            document.getElementById('delete-confirm-msg').innerText = `هل أنت متأكد من حذف الطالب "${name}" وكافة سجلاته؟`;
            document.getElementById('delete-student-modal').style.display = 'flex';
        };

        const deleteForm = document.getElementById('delete-student-form');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_delete_student_ajax');
                formData.append('nonce', '<?php echo wp_create_nonce("sm_delete_student"); ?>');
                formData.append('student_id', document.getElementById('confirm_delete_stu_id').value);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تم حذف الطالب');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        smShowNotification('خطأ: ' + res.data, true);
                    }
                })
                .catch(err => {
                    smShowNotification('حدث خطأ أثناء الاتصال بالخادم', true);
                });
            });
        }

        // Override editSmStudent default handler to incorporate Wizard reset
        const originalEditSmStudent = window.editSmStudent;
        window.editSmStudent = function(s) {
            document.getElementById('edit_stu_id').value = s.id;
            document.getElementById('edit_stu_name').value = s.name;
            document.getElementById('edit_stu_class').value = s.class_name || s.class;
            if (document.getElementById('edit_stu_section')) document.getElementById('edit_stu_section').value = s.section || '';
            document.getElementById('edit_stu_email').value = s.parent_email || '';
            document.getElementById('edit_stu_code').value = s.student_code || s.student_id || '';

            if (document.getElementById('edit_stu_phone')) document.getElementById('edit_stu_phone').value = s.guardian_phone || '';
            if (document.getElementById('edit_stu_nationality')) document.getElementById('edit_stu_nationality').value = s.nationality || '';
            if (document.getElementById('edit_stu_reg_date')) document.getElementById('edit_stu_reg_date').value = s.registration_date || '';

            if (document.getElementById('edit_stu_parent_user')) document.getElementById('edit_stu_parent_user').value = s.parent_id || '';

            // Start at first step of wizard
            goEditStep(1);
            document.getElementById('edit-student-modal').style.display = 'flex';
        };

        window.toggleAllStudents = function(master) {
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = master.checked);
            updateStudentBulkToolbar();
        };

        window.bulkDeleteSelected = function() {
            const selected = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
            if (selected.length === 0) { alert('يرجى اختيار طلاب أولاً'); return; }
            if (!confirm(`هل أنت متأكد من حذف ${selected.length} طالب نهائياً؟`)) return;

            const formData = new FormData();
            formData.append('action', 'sm_bulk_delete_students_ajax');
            formData.append('student_ids', selected.join(','));
            formData.append('nonce', '<?php echo wp_create_nonce("sm_delete_student"); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    smShowNotification(`تم حذف ${selected.length} طالب بنجاح`);
                    setTimeout(() => location.reload(), 500);
                }
            });
        };
    })();
    </script>
</div>
