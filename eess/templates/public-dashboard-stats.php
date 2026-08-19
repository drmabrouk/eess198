<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-admin-panel" dir="rtl">
    <?php
    $user_roles = (array) wp_get_current_user()->roles;
    $is_parent = in_array('sm_parent', $user_roles) || in_array('sm_student', $user_roles);
    ?>

    <!-- Header Title Card -->
    <div style="background: #ffffff; padding: 24px 30px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <h2 style="margin: 0 0 6px 0; font-size: 24px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; background: #fef2f2; border-radius: 12px; color: #dc2626;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </span>
                سجل المخالفات
            </h2>
            <p style="margin: 0; font-size: 14px; color: #64748b; font-weight: 500;">
                إدارة ومتابعة المخالفات السلوكية والانضباطية للطلاب وإجراءات التواصل مع أولياء الأمور
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <?php if (!$is_parent): ?>
                <!-- Export Reports Dropdown Menu -->
                <div style="position: relative; display: inline-block;">
                    <button type="button" onclick="const d = document.getElementById('eess-violation-export-dropdown-new'); d.style.display = d.style.display === 'none' ? 'block' : 'none'; event.stopPropagation();" class="sm-btn sm-btn-custom" style="background: #ffffff; color: #334155; border: 1px solid #cbd5e1; border-radius: 16px; padding: 0 18px; height: 44px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
                        <svg width="18" height="18" fill="none" stroke="#475569" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>تصدير التقارير</span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div id="eess-violation-export-dropdown-new" style="display: none; position: absolute; left: 0; top: 115%; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 14px; width: 230px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 99999; padding: 6px 0; text-align: right;">
                        <div style="padding: 6px 16px; font-size: 11px; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #f1f5f9;">تحميل تقارير (PDF)</div>
                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=violation_report&range=today'); ?>" target="_blank" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📄 مخالفات اليوم (PDF)</a>
                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=violation_report&range=week'); ?>" target="_blank" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📄 مخالفات الأسبوع (PDF)</a>
                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=violation_report&range=month'); ?>" target="_blank" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📄 مخالفات الشهر (PDF)</a>

                        <div style="padding: 6px 16px; font-size: 11px; color: #94a3b8; font-weight: 800; border-bottom: 1px solid #f1f5f9;">تصدير بيانات (CSV)</div>
                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_export_violations_csv&range=today&nonce='.wp_create_nonce('sm_export_action')); ?>" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📊 مخالفات اليوم (CSV)</a>
                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_export_violations_csv&range=week&nonce='.wp_create_nonce('sm_export_action')); ?>" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📊 مخالفات الأسبوع (CSV)</a>
                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_export_violations_csv&nonce='.wp_create_nonce('sm_export_action')); ?>" style="display: block; padding: 10px 16px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">📊 جميع المخالفات (CSV)</a>
                    </div>
                </div>

                <button type="button" onclick="const f=document.getElementById('violation-import-form'); f.style.display = f.style.display==='none'?'block':'none';" class="sm-btn sm-btn-custom" style="background: #ffffff; color: #334155; border: 1px solid #cbd5e1; border-radius: 16px; padding: 0 18px; height: 44px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
                    <svg width="18" height="18" fill="none" stroke="#475569" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    استيراد (CSV)
                </button>

                <?php if (current_user_can('تسجيل_مخالفة') || current_user_can('إدارة_المخالفات') || current_user_can('manage_options')): ?>
                <button type="button" onclick="if(document.getElementById('sm-global-violation-modal')){document.getElementById('sm-global-violation-modal').style.display='flex';}" class="sm-btn sm-btn-custom" style="background: #dc2626; color: #ffffff; border: none; border-radius: 16px; padding: 0 22px; height: 44px; font-weight: 800; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); transition: all 0.2s;">
                    <svg width="18" height="18" fill="none" stroke="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    تسجيل مخالفة
                </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Controls Panel -->
    <div style="background: #ffffff; padding: 22px 26px; border: 1px solid #e2e8f0; border-radius: 20px; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
        <form id="violation-filter-form" method="get">
            <input type="hidden" name="page" value="sm-dashboard">
            <input type="hidden" name="sm_tab" value="stats">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
                <?php if (!$is_parent): ?>
                <!-- Student Search -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: #334155;">
                        البحث عن طالب
                    </label>
                    <div style="position: relative;">
                        <input type="text" name="student_search" value="<?php echo esc_attr($_GET['student_search'] ?? ''); ?>" placeholder="اسم الطالب / رقم الهوية / الرقم الأكاديمي..." style="width: 100%; height: 46px; padding: 0 46px 0 16px; border: 1px solid #cbd5e1; border-radius: 14px; font-size: 13px; outline: none; background: #f8fafc; transition: all 0.2s;" onfocus="this.style.borderColor='#dc2626'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                        <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                            <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                    </div>
                </div>

                <!-- Grade Filter -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: #334155;">
                        الصف الدراسي
                    </label>
                    <div style="position: relative;">
                        <select name="class_filter" style="width: 100%; height: 46px; padding: 0 46px 0 28px; border: 1px solid #cbd5e1; border-radius: 14px; font-size: 13px; outline: none; background: #f8fafc; appearance: none; -webkit-appearance: none; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='#dc2626'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <option value="">جميع الصفوف</option>
                            <?php
                            global $wpdb;
                            $classes = $wpdb->get_col("SELECT DISTINCT class_name FROM {$wpdb->prefix}sm_students ORDER BY CAST(REPLACE(class_name, 'الصف ', '') AS UNSIGNED) ASC");
                            foreach ($classes as $c): ?>
                                <option value="<?php echo esc_attr($c); ?>" <?php selected(isset($_GET['class_filter']) && $_GET['class_filter'] == $c); ?>><?php echo esc_html($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                            <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </span>
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 10px;">▼</span>
                    </div>
                </div>

                <!-- Section Filter -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: #334155;">
                        الشعبة
                    </label>
                    <div style="position: relative;">
                        <select name="section_filter" style="width: 100%; height: 46px; padding: 0 46px 0 28px; border: 1px solid #cbd5e1; border-radius: 14px; font-size: 13px; outline: none; background: #f8fafc; appearance: none; -webkit-appearance: none; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='#dc2626'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <option value="">جميع الشعب</option>
                            <?php
                            $sections = $wpdb->get_col("SELECT DISTINCT section FROM {$wpdb->prefix}sm_students WHERE section != '' ORDER BY section ASC");
                            foreach ($sections as $s): ?>
                                <option value="<?php echo esc_attr($s); ?>" <?php selected(isset($_GET['section_filter']) && $_GET['section_filter'] == $s); ?>><?php echo esc_html($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                            <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </span>
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 10px;">▼</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Violation Type Filter -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: #334155;">
                        نوع المخالفة
                    </label>
                    <div style="position: relative;">
                        <select name="type_filter" style="width: 100%; height: 46px; padding: 0 46px 0 28px; border: 1px solid #cbd5e1; border-radius: 14px; font-size: 13px; outline: none; background: #f8fafc; appearance: none; -webkit-appearance: none; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='#dc2626'; this.style.background='#fff';" onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                            <option value="">جميع الأنواع</option>
                            <?php foreach (SM_Settings::get_violation_types() as $k => $v): ?>
                                <option value="<?php echo esc_attr($k); ?>" <?php selected(isset($_GET['type_filter']) && $_GET['type_filter'] == $k); ?>><?php echo esc_html($v); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: flex; align-items: center;">
                            <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        </span>
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 10px;">▼</span>
                    </div>
                </div>

                <!-- Submit Filter Button -->
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="submit" class="sm-btn" style="background: #dc2626; color: #ffffff; border: none; border-radius: 14px; height: 46px; padding: 0 24px; font-weight: 800; font-size: 14px; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2); transition: all 0.2s;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        تطبيق الفلترة
                    </button>
                    <div id="filter-loader" style="display:none; align-self:center;"><span class="dashicons dashicons-update spin"></span></div>
                </div>
            </div>
        </form>
    </div>

    <div id="violation-import-form" style="display:none; background: #f8fafc; padding: 30px; border: 2px dashed #cbd5e0; border-radius: 12px; margin-bottom: 30px;">
        <h3 style="margin-top:0; color:var(--sm-secondary-color);">دليل استيراد السجلات (CSV)</h3>
        
        <div style="background:#fff; padding:15px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px;">
            <p style="font-size:13px; font-weight:700; margin-bottom:10px;">هيكل ملف السجلات الصحيح:</p>
            <table style="width:100%; font-size:11px; border-collapse:collapse; text-align:center;">
                <thead>
                    <tr style="background:#edf2f7;">
                        <th style="border:1px solid #cbd5e0; padding:5px;">كود الطالب</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">النوع (سلوك/غياب/تأخر)</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الحدة (منخفضة/متوسطة/خطيرة)</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">التفاصيل</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الإجراء المتخذ</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">المكافأة/العقوبة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border:1px solid #cbd5e0; padding:5px;">STU001</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">سلوكية</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">خطيرة</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">تعدي على الزملاء</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">فصل 3 أيام</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">حرمان من الرحلة</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form method="post" enctype="multipart/form-data" onsubmit="return handleImportSubmit(this, 'sm_import_violations_csv')">
            <?php wp_nonce_field('sm_admin_action', 'sm_admin_nonce'); ?>
            <div class="sm-form-group">
                <label class="sm-label">اختر ملف CSV للمخالفات:</label>
                <input type="file" name="csv_file" accept=".csv" required>
            </div>
            <div id="import-loading" style="display:none; margin-bottom: 15px; padding: 10px; background: #ebf8ff; border-left: 4px solid #3182ce; color: #2c5282; font-weight: 700;">
                <span class="dashicons dashicons-update spin" style="margin-left: 10px;"></span>
                جاري استيراد البيانات... يرجى عدم إغلاق الصفحة.
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="sm_import_violations_csv" class="sm-btn" style="width:auto; background:#27ae60;">استيراد السجلات الآن</button>
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="sm-btn" style="width:auto; background:var(--sm-text-gray);">إلغاء</button>
            </div>
        </form>

        <script>
        function handleImportSubmit(form, btnName) {
            const btn = form.querySelector('button[name="' + btnName + '"]');
            const loader = form.querySelector('#import-loading');

            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.innerText = 'جاري المعالجة...';
            if(loader) loader.style.display = 'block';

            return true;
        }
        </script>

        <style>
        @keyframes spin { 100% { transform:rotate(360deg); } }
        .spin { animation: spin 1s linear infinite; }
        </style>
    </div>

    <?php if (current_user_can('إدارة_الطلاب')): ?>
    <div id="edit-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 800px;">
            <div class="sm-modal-header">
                <h3>تعديل الملف المعلوماتي للطالب</h3>
                <button class="sm-modal-close" onclick="document.getElementById('edit-student-modal').style.display='none'">&times;</button>
            </div>
            <form id="edit-student-form">
                <?php wp_nonce_field('sm_add_student', 'sm_nonce'); ?>
                <input type="hidden" name="student_id" id="edit_stu_id">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px; background:#f8fafc; padding:25px; border-radius:12px; border:1px solid #edf2f7;">
                    <div style="grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 5px; color: var(--sm-primary-color); font-weight: 700;">البيانات الأساسية</div>

                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الكامل للطالب:</label>
                        <input type="text" name="name" id="edit_stu_name" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الصف الدراسي:</label>
                        <select name="class_name" id="edit_stu_class" class="sm-select" required>
                            <?php
                            $academic = SM_Settings::get_academic_structure();
                            foreach ($academic['active_grades'] as $grade_num) {
                                echo "<option value='الصف $grade_num'>الصف $grade_num</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الشعبة:</label>
                        <input type="text" name="section" id="edit_stu_section" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الرقم الأكاديمي (الكود):</label>
                        <input type="text" name="student_code" id="edit_stu_code" class="sm-input" readonly>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">بريد ولي الأمر:</label>
                        <input type="email" name="parent_email" id="edit_stu_email" class="sm-input">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">رقم هاتف ولي الأمر:</label>
                        <input name="guardian_phone" id="edit_stu_phone" type="text" class="sm-input">
                    </div>
                </div>

                <div style="display:flex; gap:15px; margin-top:30px; justify-content: flex-end;">
                    <button type="submit" class="sm-btn" style="width:200px; height:50px; font-weight:800;">تحديث البيانات الآن</button>
                    <button type="button" onclick="document.getElementById('edit-student-modal').style.display='none'" class="sm-btn" style="background:#cbd5e0; color:#2d3748; width:120px;">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    (function() {
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
                        document.getElementById('edit-student-modal').style.display = 'none';
                        document.getElementById('violation-filter-form').dispatchEvent(new Event('submit'));
                    } else {
                        smShowNotification('خطأ: ' + res.data, true);
                    }
                });
            });
        }
    })();
    </script>
    <?php endif; ?>

    <div id="edit-record-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 800px;">
            <div class="sm-modal-header">
                <h3>تعديل بيانات المخالفة</h3>
                <button class="sm-modal-close" onclick="document.getElementById('edit-record-modal').style.display='none'">&times;</button>
            </div>
            <form method="post" id="edit-record-form" class="sm-form-container">
                <?php wp_nonce_field('sm_record_action', 'sm_nonce'); ?>
                <input type="hidden" name="record_id" id="edit_record_id">
                
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                    <div class="sm-form-group" style="margin-bottom:0;">
                        <label class="sm-label">درجة المخالفة (المستوى):</label>
                        <select name="degree" id="edit_violation_degree" class="sm-select" onchange="updateEditHierarchicalViolations()" required>
                            <option value="1">المستوى الأول (بسيطة)</option>
                            <option value="2">المستوى الثاني (متوسطة)</option>
                            <option value="3">المستوى الثالث (جسيمة)</option>
                            <option value="4">المستوى الرابع (شديدة الخطورة)</option>
                        </select>
                    </div>

                    <div class="sm-form-group" style="margin-bottom:0;">
                        <label class="sm-label">البند القانوني / نوع المخالفة:</label>
                        <select name="violation_code" id="edit_violation_code_select" class="sm-select" onchange="onEditViolationSelected()" required>
                            <option value="">-- اختر البند --</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="sm-form-group">
                        <label class="sm-label">تصنيف الموقف:</label>
                        <select name="classification" id="edit_classification" class="sm-select">
                            <option value="general">عام</option>
                            <option value="inside_class">داخل الفصل</option>
                            <option value="yard">في الساحة</option>
                            <option value="labs">في المختبرات</option>
                            <option value="bus">الحافلة المدرسية</option>
                        </select>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-label">النقاط المستحقة:</label>
                        <input type="number" name="points" id="edit_violation_points" class="sm-input" value="0">
                    </div>
                    <input type="hidden" name="type" id="edit_hidden_violation_type">
                    <input type="hidden" name="severity" id="edit_violation_severity">
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">الإجراء المتخذ:</label>
                    <input type="text" name="action_taken" id="edit_action_taken" class="sm-input">
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">التفاصيل:</label>
                    <textarea name="details" id="edit_details" class="sm-textarea" rows="3"></textarea>
                </div>

                <div style="display:flex; gap:12px; margin-top: 20px; justify-content: flex-end;">
                    <button type="submit" name="sm_update_record" class="sm-btn" style="height: 45px; min-width: 150px;">حفظ التغييرات</button>
                    <button type="button" onclick="document.getElementById('edit-record-modal').style.display='none'" class="sm-btn" style="background:var(--sm-text-gray); height: 45px; min-width: 100px;">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const hViolations = <?php echo json_encode(SM_Settings::get_hierarchical_violations()); ?>;

    function updateEditHierarchicalViolations(selectedCode = '') {
        const degree = document.getElementById('edit_violation_degree').value;
        const select = document.getElementById('edit_violation_code_select');

        select.innerHTML = '<option value="">-- اختر البند --</option>';
        if (!degree || !hViolations[degree]) return;

        Object.keys(hViolations[degree]).forEach(code => {
            const v = hViolations[degree][code];
            const opt = document.createElement('option');
            opt.value = code;
            opt.innerText = code + ' - ' + v.name;
            if (code === selectedCode) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function onEditViolationSelected() {
        const degree = document.getElementById('edit_violation_degree').value;
        const code = document.getElementById('edit_violation_code_select').value;
        if (!degree || !code || !hViolations[degree][code]) return;

        const v = hViolations[degree][code];
        document.getElementById('edit_violation_points').value = v.points;
        document.getElementById('edit_action_taken').value = v.action;
        document.getElementById('edit_hidden_violation_type').value = v.name;

        const sev = document.getElementById('edit_violation_severity');
        if (degree == 1) sev.value = 'low';
        else if (degree == 2) sev.value = 'medium';
        else sev.value = 'high';
    }

    function editSmRecord(record) {
        document.getElementById('edit_record_id').value = record.id;
        document.getElementById('edit_violation_degree').value = record.degree || 1;
        document.getElementById('edit_classification').value = record.classification || 'general';
        document.getElementById('edit_violation_points').value = record.points || 0;
        document.getElementById('edit_action_taken').value = record.action_taken || '';
        document.getElementById('edit_details').value = record.details || '';
        document.getElementById('edit_hidden_violation_type').value = record.type || '';
        document.getElementById('edit_violation_severity').value = record.severity || 'low';

        updateEditHierarchicalViolations(record.violation_code);
        document.getElementById('edit-record-modal').style.display = 'flex';
    }
    </script>

    <!-- Violations Table Container -->
    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 24px;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: right;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 16px 20px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 2px solid #e2e8f0; width: 26%;">الطالب</th>
                        <th style="padding: 16px 20px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 2px solid #e2e8f0; width: 20%;">المدرسة / الصف / الشعبة</th>
                        <th style="padding: 16px 20px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 2px solid #e2e8f0; width: 14%;">التاريخ واليوم</th>
                        <th style="padding: 16px 20px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 2px solid #e2e8f0; width: 18%;">بند المخالفة والدرجة</th>
                        <th style="padding: 16px 20px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 2px solid #e2e8f0; text-align: center; width: 6%;">تكرار</th>
                        <th style="padding: 16px 20px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 2px solid #e2e8f0; text-align: center; width: 8%;">الشدة / الحالة</th>
                        <th style="padding: 16px 20px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 2px solid #e2e8f0; text-align: center; width: 18%;">الإجراءات الإدارية</th>
                    </tr>
                </thead>
                <tbody id="violations-table-body">
                    <?php include SM_PLUGIN_DIR . 'templates/partials/violations-table-rows.php'; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="view-record-modal" class="sm-modal-overlay" style="display: none;">
        <div class="sm-modal-content" style="max-width: 650px; border-radius: 20px; padding: 28px;">
            <div class="sm-modal-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    تفاصيل المخالفة السلوكية
                </h3>
                <button type="button" class="sm-modal-close" onclick="document.getElementById('view-record-modal').style.display='none'" style="background: none; border: none; font-size: 24px; color: #94a3b8; cursor: pointer;">&times;</button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="background: #f8fafc; border-radius: 14px; padding: 16px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 14px;">
                    <div id="view_stu_photo" style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; background: #cbd5e1; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; flex-shrink: 0;"></div>
                    <div>
                        <div id="view_stu_name" style="font-size: 16px; font-weight: 800; color: #1e293b;"></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                            المدرسة: <span id="view_school_name" style="font-weight: 700; color: #334155;"></span> | الصف والشعبة: <span id="view_class_sec" style="font-weight: 700; color: #334155;"></span>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                        <span style="font-size: 11px; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px;">كود المخالفة / البند</span>
                        <span id="view_violation_code" style="font-size: 14px; font-weight: 800; color: #1e293b;"></span>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                        <span style="font-size: 11px; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px;">درجة المخالفة</span>
                        <span id="view_degree" style="font-size: 14px; font-weight: 800; color: #dc2626;"></span>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                        <span style="font-size: 11px; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px;">التاريخ</span>
                        <span id="view_date" style="font-size: 13px; font-weight: 700; color: #334155;"></span>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                        <span style="font-size: 11px; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px;">عدد مرات التكرار</span>
                        <span id="view_recurrence" style="font-size: 13px; font-weight: 800; color: #334155;"></span>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 12px;">
                    <span style="font-size: 11px; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px;">تفاصيل الموقف السلوكي</span>
                    <p id="view_details_text" style="margin: 0; font-size: 13px; color: #334155; line-height: 1.6; font-weight: 500;"></p>
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 12px;">
                    <span style="font-size: 11px; color: #64748b; font-weight: 700; display: block; margin-bottom: 4px;">الإجراء المتخذ</span>
                    <p id="view_action_text" style="margin: 0; font-size: 13px; color: #16a34a; line-height: 1.6; font-weight: 700;"></p>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('view-record-modal').style.display='none'" class="sm-btn" style="background: #475569; color: #fff; border-radius: 12px; padding: 0 24px; height: 42px; font-weight: 700;">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- Delete Record Confirmation Modal -->
    <div id="delete-record-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 400px; text-align: center; border-radius: 20px; padding: 28px;">
            <div style="color: #dc2626; font-size: 40px; margin-bottom: 15px;">
                <svg width="48" height="48" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="margin: 0 auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 style="margin:0 0 10px 0; border:none; font-size: 18px; font-weight: 800; color: #1e293b;">تأكيد حذف المخالفة</h3>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">هل أنت متأكد من حذف هذا السجل نهائياً؟ لا يمكن التراجع عن هذه العملية.</p>
            <input type="hidden" id="confirm_delete_record_id">
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button onclick="executeDeleteRecord()" class="sm-btn" style="background: #dc2626; color: #fff; border-radius: 12px; height: 42px; padding: 0 20px; font-weight: 800;">حذف نهائي</button>
                <button onclick="document.getElementById('delete-record-modal').style.display='none'" class="sm-btn" style="background: #cbd5e1; color: #334155; border-radius: 12px; height: 42px; padding: 0 20px; font-weight: 700;">تراجع</button>
            </div>
        </div>
    </div>

    <script>
    function exportViolationPDF() {
        const student = document.querySelector('input[name="student_search"]').value;
        const grade = document.querySelector('select[name="class_filter"]').value;
        const section = document.querySelector('select[name="section_filter"]').value;
        const type = document.querySelector('select[name="type_filter"]').value;

        let url = '<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=violation_report'); ?>';
        if (student) url += '&search=' + encodeURIComponent(student);
        if (grade) url += '&class_filter=' + encodeURIComponent(grade);
        if (section) url += '&section_filter=' + encodeURIComponent(section);
        if (type) url += '&type_filter=' + encodeURIComponent(type);

        window.open(url, '_blank');
    }

    (function() {
        // AJAX Filtering Logic
        const filterForm = document.getElementById('violation-filter-form');
        if (filterForm) {
            filterForm.onsubmit = function(e) {
                e.preventDefault();
                const loader = document.getElementById('filter-loader');
                const tbody = document.getElementById('violations-table-body');

                if (loader) loader.style.display = 'inline-block';
                tbody.style.opacity = '0.5';

                const formData = new FormData(this);
                formData.append('action', 'sm_filter_violations');
                formData.append('nonce', '<?php echo wp_create_nonce("sm_record_action"); ?>');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        tbody.innerHTML = res.data.html;
                    }
                })
                .finally(() => {
                    if (loader) loader.style.display = 'none';
                    tbody.style.opacity = '1';
                });
            };
        }

        window.viewViolationDetails = function(record) {
            document.getElementById('view_stu_name').innerText = record.student_name || '---';
            document.getElementById('view_school_name').innerText = record.school_name || 'المدرسة الرئيسية';
            document.getElementById('view_class_sec').innerText = (record.class_name || '') + ' ' + (record.section || '');
            document.getElementById('view_violation_code').innerText = record.violation_code || record.type || '---';
            document.getElementById('view_degree').innerText = 'المستوى / الدرجة ' + (record.degree || 1);
            document.getElementById('view_date').innerText = record.created_at || '---';
            document.getElementById('view_recurrence').innerText = record.recurrence_count || 1;
            document.getElementById('view_details_text').innerText = record.details || 'لا توجد تفاصيل إضافية مسجلة.';
            document.getElementById('view_action_text').innerText = record.action_taken || 'لم يتم تسجيل إجراء إداري بعد.';

            const photoBox = document.getElementById('view_stu_photo');
            if (record.photo_url) {
                photoBox.innerHTML = '<img src="' + record.photo_url + '" style="width:100%; height:100%; object-fit:cover;" />';
            } else {
                photoBox.innerHTML = '<svg width="24" height="24" fill="#94a3b8" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>';
            }

            document.getElementById('view-record-modal').style.display = 'flex';
        };

        window.updateRecordStatus = function(id, status) {
            const formData = new FormData();
            formData.append('action', 'sm_update_record_status');
            formData.append('record_id', id);
            formData.append('status', status);
            formData.append('nonce', '<?php echo wp_create_nonce("sm_record_action"); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    if (typeof smShowNotification === 'function') {
                        smShowNotification('تم تحديث حالة المخالفة بنجاح');
                    }
                    filterForm.dispatchEvent(new Event('submit'));
                }
            });
        };

        window.markAsContacted = function(recordId) {
            const formData = new FormData();
            formData.append('action', 'sm_mark_contacted');
            formData.append('record_id', recordId);
            formData.append('nonce', '<?php echo wp_create_nonce("sm_record_action"); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    filterForm.dispatchEvent(new Event('submit'));
                }
            });
        };

        window.editSmStudentFromStats = function(s) {
            // Map keys if needed to match global editSmStudent expectations
            // expected keys: id, name, class_name, section, parent_email, guardian_phone, student_id (code)
            if (typeof window.editSmStudent === 'function') {
                window.editSmStudent(s);
            } else {
                console.error('editSmStudent function not found');
            }
        };

        window.confirmDeleteRecord = function(id) {
            document.getElementById('confirm_delete_record_id').value = id;
            document.getElementById('delete-record-modal').style.display = 'flex';
        };

        window.executeDeleteRecord = function() {
            const id = document.getElementById('confirm_delete_record_id').value;
            const formData = new FormData();
            formData.append('action', 'sm_delete_record_ajax');
            formData.append('record_id', id);
            formData.append('nonce', '<?php echo wp_create_nonce("sm_record_action"); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    smShowNotification('تم حذف السجل بنجاح');
                    const row = document.getElementById('record-row-' + id);
                    if (row) row.remove();
                    document.getElementById('delete-record-modal').style.display = 'none';
                }
            });
        };
    })();
    </script>

    <style>
    .sm-record-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.2s; }
    .sm-action-icon-btn { width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; text-decoration: none; font-size: 16px; }
    </style>
</div>
<style>
@media print {
    body * { visibility: hidden; }
    .sm-admin-panel, .sm-admin-panel * { visibility: visible; }
    .sm-admin-panel { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
