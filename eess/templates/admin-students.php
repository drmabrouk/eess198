<?php if (!defined('ABSPATH')) exit; ?>
<?php
$is_admin = current_user_can('شؤون_الطلاب') || current_user_can('manage_options');
$import_results = get_transient('sm_import_results_' . get_current_user_id());
if ($import_results) {
    delete_transient('sm_import_results_' . get_current_user_id());
}

// Data query & stats
global $wpdb;
$students_list = is_array($students) ? $students : array();
$total_students_count = count($students_list);
?>
<div class="sm-content-wrapper" dir="rtl" style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; color: #1e293b;">

    <?php if ($import_results): ?>
        <div style="background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; overflow: hidden; box-shadow: 0 4px 18px rgba(0,0,0,0.02);">
            <div style="background: #f8fafc; padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin:0; color: #0f172a; font-weight: 800; font-size: 15px;">تقرير استيراد الطلاب الأخير</h4>
                <span style="font-size: 12px; color: #64748b; font-weight: 700;">إجمالي السجلات المعالجة: <?php echo $import_results['total']; ?></span>
            </div>
            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div style="background: #f0fff4; padding: 14px; border-radius: 12px; border: 1px solid #c6f6d5; text-align: center;">
                        <div style="font-size: 22px; font-weight: 800; color: #2f855a;"><?php echo $import_results['success'] - ($import_results['duplicate'] ?? 0); ?></div>
                        <div style="font-size: 11.5px; color: #38a169; font-weight: 700;">سجلات جديدة</div>
                    </div>
                    <div style="background: #e6fffa; padding: 14px; border-radius: 12px; border: 1px solid #b2f5ea; text-align: center;">
                        <div style="font-size: 22px; font-weight: 800; color: #2c7a7b;"><?php echo $import_results['generated'] ?? 0; ?></div>
                        <div style="font-size: 11.5px; color: #319795; font-weight: 700;">أكواد تم توليدها</div>
                    </div>
                    <div style="background: #ebf8ff; padding: 14px; border-radius: 12px; border: 1px solid #bee3f8; text-align: center;">
                        <div style="font-size: 22px; font-weight: 800; color: #2b6cb0;"><?php echo $import_results['duplicate'] ?? 0; ?></div>
                        <div style="font-size: 11.5px; color: #3182ce; font-weight: 700;">سجلات مكررة</div>
                    </div>
                    <div style="background: #fffaf0; padding: 14px; border-radius: 12px; border: 1px solid #feebc8; text-align: center;">
                        <div style="font-size: 22px; font-weight: 800; color: #c05621;"><?php echo $import_results['warning']; ?></div>
                        <div style="font-size: 11.5px; color: #dd6b20; font-weight: 700;">تنبيهات</div>
                    </div>
                    <div style="background: #fff5f5; padding: 14px; border-radius: 12px; border: 1px solid #fed7d7; text-align: center;">
                        <div style="font-size: 22px; font-weight: 800; color: #c53030;"><?php echo $import_results['error']; ?></div>
                        <div style="font-size: 11.5px; color: #e53e3e; font-weight: 700;">أخطاء</div>
                    </div>
                </div>

                <?php if (!empty($import_results['details'])): ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; max-height: 220px; overflow-y: auto;">
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

    <!-- 1. Header Banner Card (Duplicates Student Discipline Records Design) -->
    <div style="background: #ffffff; padding: 22px 28px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 20px; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; background: #ebf8ff; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #2563eb; border: 1px solid #bee3f8; flex-shrink: 0;">
                <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <div>
                <h2 style="margin: 0 0 4px 0; font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;">
                    إدارة شؤون الطلاب — Student Affairs
                </h2>
                <p style="margin: 0; font-size: 13px; color: #64748b; font-weight: 500;">
                    المركز الرئيسي لإدارة بيانات الطلاب، الملفات الأكاديمية والشخصية، السجلات المدرسية، واستيراد وتصدير ملفات البيانات المعتمدة
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Export / Print Reports Dropdown -->
            <div style="position: relative; display: inline-block;">
                <button type="button" onclick="const d = document.getElementById('eess-students-export-dropdown'); d.style.display = d.style.display === 'none' ? 'block' : 'none'; event.stopPropagation();" class="eess-hdr-btn" style="background: #f8fafc !important; color: #334155 !important; border: 1px solid #cbd5e1 !important; border-radius: 12px; padding: 0 16px; height: 42px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    <svg width="18" height="18" fill="none" stroke="#334155" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span style="color: #334155 !important;">تصدير التقارير</span>
                    <svg width="12" height="12" fill="none" stroke="#334155" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div id="eess-students-export-dropdown" style="display: none; position: absolute; left: 0; top: 115%; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 14px; width: 240px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 99999; padding: 6px 0; text-align: right;">
                    <div style="padding: 6px 16px; font-size: 11px; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #f1f5f9;">تصدير واستيراد البيانات (Excel/CSV)</div>
                    <a href="<?php echo admin_url('admin-ajax.php?action=sm_export_students_csv&nonce=' . wp_create_nonce('sm_admin_action')); ?>" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📊 تصدير جميع الطلاب (Excel/CSV)</a>
                    <a href="<?php echo admin_url('admin-ajax.php?action=sm_download_student_import_template'); ?>" target="_blank" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📥 تحميل نموذج الاستيراد الرسمى (11 عمود)</a>

                    <div style="padding: 6px 16px; font-size: 11px; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #f1f5f9;">طباعة البطاقات والتقارير</div>
                    <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=id_card'); ?>" target="_blank" style="display: block; padding: 10px 16px; color: #16a34a; font-size: 12px; font-weight: 700; text-decoration: none; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">🖨️ طباعة بطاقات الهوية الأكاديمية</a>
                </div>
            </div>

            <!-- Secondary Action: Import -->
            <button type="button" onclick="const f=document.getElementById('csv-import-form'); f.style.display = f.style.display==='none'?'block':'none';" class="eess-hdr-btn" style="background: #f8fafc !important; color: #334155 !important; border: 1px solid #cbd5e1 !important; border-radius: 12px; padding: 0 16px; height: 42px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <svg width="18" height="18" fill="none" stroke="#334155" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                <span style="color: #334155 !important;">استيراد</span>
            </button>

            <!-- Primary Action: Add Student -->
            <?php if ($is_admin): ?>
            <button type="button" onclick="document.getElementById('add-single-student-modal').style.display='flex'" class="sm-btn sm-btn-custom" style="background: #2563eb; color: #ffffff; border: none; border-radius: 12px; padding: 0 20px; height: 42px; font-weight: 800; font-size: 13.5px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); transition: all 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                <svg width="18" height="18" fill="none" stroke="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>إضافة طالب جديد</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. Search & Filtering Card -->
    <div style="background: #ffffff; padding: 20px 24px; border: 1px solid #e2e8f0; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
        <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: end;">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? 'sm-dashboard'); ?>">
            <input type="hidden" name="sm_tab" value="students">

            <!-- Student Search -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-size: 12.5px; font-weight: 700; color: #334155;">
                    اسم الطالب / الكود
                </label>
                <div style="position: relative;">
                    <input type="text" name="student_search" value="<?php echo esc_attr($_GET['student_search'] ?? ''); ?>" placeholder="بحث بالاسم، الكود، الهوية..." style="width: 100%; height: 42px; padding: 0 38px 0 14px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 13px; outline: none; background: #f8fafc; transition: all 0.2s;" onfocus="this.style.borderColor='#2563eb'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                        <svg width="16" height="16" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                </div>
            </div>

            <!-- Grade Filter -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-size: 12.5px; font-weight: 700; color: #334155;">
                    الصف الدراسي
                </label>
                <div style="position: relative;">
                    <select name="class_filter" style="width: 100%; height: 42px; padding: 0 38px 0 26px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 13px; outline: none; background: #f8fafc; appearance: none; -webkit-appearance: none; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='#2563eb'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                        <option value="">جميع الصفوف</option>
                        <?php
                        $academic = SM_Settings::get_academic_structure();
                        foreach ($academic['active_grades'] as $grade_num) {
                            $grade_label = 'الصف ' . $grade_num;
                            echo '<option value="' . esc_attr($grade_label) . '" ' . selected(isset($_GET['class_filter']) && $_GET['class_filter'] == $grade_label, true, false) . '>' . esc_html($grade_label) . '</option>';
                        }
                        ?>
                    </select>
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                        <svg width="16" height="16" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </span>
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 9px;">▼</span>
                </div>
            </div>

            <!-- Section Filter -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-size: 12.5px; font-weight: 700; color: #334155;">
                    الشعبة / الفصل
                </label>
                <div style="position: relative;">
                    <input type="text" name="section_filter" value="<?php echo esc_attr($_GET['section_filter'] ?? ''); ?>" placeholder="مثال: أ" list="existing-sections" style="width: 100%; height: 42px; padding: 0 38px 0 14px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 13px; outline: none; background: #f8fafc; transition: all 0.2s;" onfocus="this.style.borderColor='#2563eb'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                    <datalist id="existing-sections">
                        <?php
                        $sections = $wpdb->get_col("SELECT DISTINCT section FROM {$wpdb->prefix}sm_students WHERE section != '' ORDER BY section ASC");
                        foreach ($sections as $sec) echo "<option value='".esc_attr($sec)."'>";
                        ?>
                    </datalist>
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                        <svg width="16" height="16" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </span>
                </div>
            </div>

            <!-- Teacher Filter -->
            <div>
                <label style="display: block; margin-bottom: 6px; font-size: 12.5px; font-weight: 700; color: #334155;">
                    المعلم المربّي
                </label>
                <div style="position: relative;">
                    <select name="teacher_filter" style="width: 100%; height: 42px; padding: 0 38px 0 26px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 13px; outline: none; background: #f8fafc; appearance: none; -webkit-appearance: none; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='#2563eb'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                        <option value="">جميع المعلمين</option>
                        <?php
                        $teachers = get_users(array('role' => 'sm_teacher'));
                        foreach ($teachers as $t) {
                            echo '<option value="' . $t->ID . '" ' . selected(isset($_GET['teacher_filter']) && $_GET['teacher_filter'] == $t->ID, true, false) . '>' . esc_html($t->display_name) . '</option>';
                        }
                        ?>
                    </select>
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                        <svg width="16" height="16" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </span>
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 9px;">▼</span>
                </div>
            </div>

            <!-- Apply Filters Button -->
            <div>
                <button type="submit" class="sm-btn" style="background: #2563eb; color: #ffffff; border: none; border-radius: 12px; height: 42px; padding: 0 22px; font-weight: 800; font-size: 13.5px; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); transition: all 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>تطبيق الفلترة</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Chunked File Upload Progress Form & Documentation -->
    <div id="csv-import-form" style="display:none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
            <h4 style="margin:0; color:#0f172a; font-weight: 800; font-size: 16px;">استيراد ذكي لملف الطلاب الشامل (Excel / CSV)</h4>
            <a href="<?php echo admin_url('admin-ajax.php?action=sm_download_student_import_template'); ?>" target="_blank" class="sm-btn" style="background: #2563eb; color: white !important; font-size: 11px; padding: 6px 16px; width: auto; height: 32px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                📥 تحميل نموذج الاستيراد المعتمد (11 عمود)
            </a>
        </div>

        <p style="font-size:12.5px; color:#64748b; line-height:1.6; margin-bottom:15px;">
            يرجى اختيار ملف الطلاب المعتمد بصيغة CSV/Excel. يدعم النظام بنظام الدفعات (Chunking) لمعالجة الملفات الضخمة وتحديث بيانات الطلاب الحاليين تلقائياً عند مطابقة الكود أو الهوية.
        </p>

        <!-- Official Column Structure Documentation Card -->
        <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
            <div style="font-size: 12.5px; font-weight: 800; color: #1e293b; margin-bottom: 10px;">📋 دليل ترتيب أعمدة نموذج الاستيراد الرسمي (الأعمدة من A إلى K):</div>
            <div style="max-height: 180px; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; text-align: right;">
                    <thead>
                        <tr style="background: #f1f5f9; position: sticky; top: 0; color: #334155;">
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1; width: 50px;">العمود</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1;">اسم الحقل</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1; width: 90px;">الحالة</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1;">الصيغة التوضيحية</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #cbd5e1;">مثال</th>
                        </tr>
                    </thead>
                    <tbody style="color: #475569;">
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">A</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">كود الطالب (Student Code)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #2563eb;">اختياري (تلقائي)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">نصي / رقمي</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">STU-1001</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">B</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">الاسم الكامل (Full Name)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #dc2626; font-weight:700;">إجباري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">نص ثلاثي أو رباعي</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">علي أحمد عبدالله</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">C</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">الهوية الوطنية (National ID)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #2563eb;">اختياري (فريد)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">أرقام فقط</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">784199012345678</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">D</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">الصف الدراسي (Grade)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #dc2626; font-weight:700;">إجباري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">الصف 1 إلى 12</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">الصف 5</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">E</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">الشعبة / الفصل (Section)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b;">اختياري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">حرف أو رقم</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">أ</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">F</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">الجنسية (Nationality)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b;">اختياري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">اسم الدولة</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">إماراتي</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">G</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">تاريخ التسجيل (Reg Date)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b;">اختياري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">YYYY-MM-DD</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">2024-09-01</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">H</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">بريد ولي الأمر (Email)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b;">اختياري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">بريد إلكتروني صالح</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">parent@domain.com</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">I</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">هاتف ولي الأمر (Phone)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b;">اختياري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">أرقام الهاتف</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">0501234567</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">J</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">رابط الصورة (Photo URL)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b;">اختياري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">رابط URL مباشر</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">https://site.com/p.jpg</td></tr>
                        <tr><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-weight:700;">K</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">معرف المدرسة (School ID)</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9; color: #64748b;">اختياري</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">رقم المعرف</td><td style="padding: 6px 10px; border-bottom: 1px solid #f1f5f9;">1</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

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
                <div id="import-progress-bar" style="background:#2563eb; width:0%; height:100%; transition:0.3s;"></div>
            </div>
        </div>
    </div>

    <!-- Dynamic Bulk Actions Toolbar (initially hidden, shows ONLY when checkboxes are checked) -->
    <div id="student-bulk-actions-toolbar" style="display: none; gap: 10px; margin-bottom: 15px; align-items: center; background: #fff5f5; padding: 12px 20px; border-radius: 12px; border: 1px solid #fed7d7;">
        <span style="font-size: 12.5px; font-weight: 700; color: #c53030;">الإجراءات الجماعية للطلاب المحددين:</span>
        <button onclick="bulkDeleteSelected()" class="sm-btn" style="background: #dc2626; font-size: 11.5px; padding: 5px 16px; width: auto; height: 32px; border-radius: 8px;">حذف المحدد نهائياً</button>
    </div>

    <!-- 3. Records Table Card -->
    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 24px;">

        <!-- Table Top Control Bar -->
        <div style="padding: 18px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; background: #ffffff;">
            <!-- Left Header Info -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: 800; font-size: 15px; color: #0f172a;">قائمة الطلاب المسجلين</span>
                <span style="display: inline-flex; align-items: center; padding: 4px 12px; background: #ebf8ff; color: #2563eb; border-radius: 12px; font-size: 12px; font-weight: 800;">
                    <?php echo $total_students_count; ?> طالب
                </span>
            </div>

            <!-- Right Info -->
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">
                السجلات الأكاديمية والشخصية المعتمدة
            </div>
        </div>

        <!-- Table Responsive Wrapper -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: right;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="width: 40px; text-align: center; padding: 14px 10px; border-bottom: 2px solid #e2e8f0;"><input type="checkbox" onclick="toggleAllStudents(this)"></th>
                        <th style="padding: 14px 18px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; width: 30%;">بيانات الطالب والهوية</th>
                        <th style="padding: 14px 18px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; width: 22%;">التسكين الأكاديمي والمدرسة</th>
                        <th style="padding: 14px 18px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; width: 18%;">المعلم المربّي</th>
                        <th style="padding: 14px 18px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center; width: 10%;">النقاط السلوكية</th>
                        <th style="padding: 14px 18px; font-size: 12.5px; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; text-align: center; width: 20%;">الإجراءات والخيارات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students_list)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 50px 20px; font-weight: 700;">لا يوجد طلاب يطابقون شروط البحث حالياً في قاعدة البيانات.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students_list as $student): ?>
                            <tr id="stu-row-<?php echo $student->id; ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="text-align: center; padding: 14px 10px;"><input type="checkbox" class="student-checkbox" value="<?php echo $student->id; ?>" onchange="updateStudentBulkToolbar()"></td>

                                <!-- Student Cell (Matching Discipline Design with Circular Image, Name, Code & National ID Badges) -->
                                <td style="padding: 14px 18px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php if (!empty($student->photo_url)): ?>
                                            <img src="<?php echo esc_url($student->photo_url); ?>" style="width: 44px; height: 44px; border-radius: 50% !important; object-fit: cover; border: 2px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                        <?php else: ?>
                                            <div style="background: #f1f5f9; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #94a3b8; border: 1px solid #e2e8f0; font-size: 20px;">
                                                👤
                                            </div>
                                        <?php endif; ?>
                                        <div style="line-height: 1.4;">
                                            <div style="font-weight: 800; font-size: 14px; color: #0f172a;"><?php echo esc_html($student->name); ?></div>
                                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                                                <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-size: 10.5px; font-weight: 800; font-family: monospace;">
                                                    كود: <?php echo esc_html($student->student_code); ?>
                                                </span>
                                                <?php if (!empty($student->national_id)): ?>
                                                    <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: #f1f5f9; color: #475569; border-radius: 6px; font-size: 10.5px; font-weight: 700; font-family: monospace;">
                                                        هوية: <?php echo esc_html($student->national_id); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($student->nationality)): ?>
                                                    <span style="display: inline-flex; align-items: center; padding: 2px 6px; background: #fef3c7; color: #92400e; border-radius: 6px; font-size: 10px; font-weight: 700;">
                                                        <?php echo esc_html($student->nationality); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Placement Cell -->
                                <td style="padding: 14px 18px;">
                                    <div style="font-weight: 700; font-size: 12.5px; color: #334155; margin-bottom: 4px;">
                                        <?php echo esc_html($student->school_name ?? 'المدرسة الرئيسية'); ?>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: #f1f5f9; color: #334155; border-radius: 6px; font-size: 11px; font-weight: 800;">
                                            <?php echo esc_html($student->class_name); ?>
                                        </span>
                                        <?php if (!empty($student->section)): ?>
                                            <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: #e2e8f0; color: #0f172a; border-radius: 6px; font-size: 11px; font-weight: 800;">
                                                شعبة <?php echo esc_html($student->section); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Teacher Cell -->
                                <td style="padding: 14px 18px;">
                                    <?php
                                    $teacher = $student->teacher_id ? get_userdata($student->teacher_id) : null;
                                    if ($teacher): ?>
                                        <div style="font-weight: 700; font-size: 12.5px; color: #0f172a;"><?php echo esc_html($teacher->display_name); ?></div>
                                        <div style="font-size: 11px; color: #94a3b8;"><?php echo esc_html($teacher->user_email); ?></div>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1; font-style: italic; font-size: 12px;">غير معيّن</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Behavior Points Cell -->
                                <td style="padding: 14px 18px; text-align: center;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 800; background: <?php echo $student->behavior_points > 10 ? '#fee2e2' : ($student->behavior_points > 4 ? '#fef3c7' : '#dcfce7'); ?>; color: <?php echo $student->behavior_points > 10 ? '#dc2626' : ($student->behavior_points > 4 ? '#d97706' : '#15803d'); ?>;">
                                        <?php echo intval($student->behavior_points); ?> نقطة
                                    </span>
                                </td>

                                <!-- Standardized Circular Action Buttons (WhatsApp, Print, Edit, Delete, Details) -->
                                <td style="padding: 14px 18px; text-align: center;">
                                    <div style="display: flex; items-center; justify-content: center; gap: 6px;">
                                        <!-- Report PDF Button -->
                                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_print_student_full_report&student_id=' . $student->id); ?>" target="_blank" title="التقرير الشامل (PDF)" style="width: 36px; height: 36px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                            🖨️
                                        </a>

                                        <!-- Behavioral Profile Drawer -->
                                        <button type="button" onclick="viewSmStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)" title="الملف السلوكي والتحليلي" style="width: 36px; height: 36px; border-radius: 50%; background: #e0f2fe; color: #0284c7; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                            📋
                                        </button>

                                        <?php if ($is_admin): ?>
                                            <!-- Credentials Button -->
                                            <button type="button" onclick="showStudentCreds('<?php echo esc_js($student->student_code); ?>', '<?php echo esc_js(get_user_meta($student->parent_user_id, 'sm_temp_pass', true)); ?>', '<?php echo esc_js($student->name); ?>', <?php echo $student->id; ?>)" title="حساب الدخول الأكاديمي" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #334155; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                                🔑
                                            </button>

                                            <!-- Edit Student Button -->
                                            <button type="button" onclick='editSmStudent(<?php echo json_encode($student); ?>)' title="تعديل الطالب" style="width: 36px; height: 36px; border-radius: 50%; background: #dbeafe; color: #2563eb; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                                ✏️
                                            </button>

                                            <!-- Delete Student Button -->
                                            <button type="button" onclick="confirmDeleteStudent(<?php echo $student->id; ?>, '<?php echo esc_js($student->name); ?>')" title="حذف الطالب نهائياً" style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; color: #dc2626; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                                🗑️
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer Info -->
        <div style="padding: 16px 24px; border-top: 1px solid #f1f5f9; background: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 13px; color: #64748b; font-weight: 600;">
                عرض إجمالي <span style="color: #0f172a; font-weight: 800;"><?php echo $total_students_count; ?></span> طالب في المنظومة
            </div>
            <div style="font-size: 12px; color: #94a3b8; font-weight: 600;">
                تاريخ المزامنة: <?php echo date('Y-m-d'); ?>
            </div>
        </div>
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

    <!-- UNIFIED STUDENT PROFILE EDIT MODAL -->
    <?php if ($is_admin): ?>
        <?php include SM_PLUGIN_DIR . 'templates/partials/student-profile-edit-modal.php'; ?>
    <?php endif; ?>
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
        // Close export dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('eess-students-export-dropdown');
            if (dropdown && dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        });

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
