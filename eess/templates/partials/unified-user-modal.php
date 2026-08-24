<?php
if (!defined('ABSPATH')) exit;

$institutions = class_exists('EESS_Org_Helper') ? EESS_Org_Helper::get_institutions() : array();
$all_schools  = class_exists('EESS_Org_Helper') ? EESS_Org_Helper::get_all_schools() : array();
$subjects     = class_exists('SM_Settings') ? SM_Settings::get_subjects() : array();
$departments  = class_exists('SM_Settings') ? SM_Settings::get_departments() : array();
?>

<!-- UNIFIED USER & EMPLOYEE MANAGEMENT MODAL -->
<div id="unified-user-modal" class="sm-modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 999999; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(5px); position: fixed; inset: 0; padding: 10px;">
    <div class="sm-modal-content" style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 900px; max-height: 92vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); border: 1px solid #cbd5e1; font-family: 'Cairo', sans-serif; box-sizing: border-box; overflow: hidden; display: flex; flex-direction: column;" dir="rtl">

        <!-- Modal Header (Flush Full Width) -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #0f172a; color: #ffffff; border-bottom: 1px solid #1e293b; box-sizing: border-box; width: 100%;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="dashicons dashicons-admin-users" style="font-size: 24px; width: 24px; height: 24px; color: #ffffff;"></span>
                <h3 id="u_modal_title" style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff; font-family: 'Cairo', sans-serif;">تعديل بيانات الحساب وتعيينات الموظف</h3>
            </div>
            <button type="button" class="sm-modal-close" onclick="eessCloseUnifiedUserModal()" style="background: transparent; border: none; font-size: 26px; color: #ffffff; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- 4-Step Indicator Bar -->
        <div style="padding: 14px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; position: relative;">
            <div style="position: absolute; top: 50%; left: 8%; right: 8%; height: 2px; background: #e2e8f0; z-index: 1; transform: translateY(-50%);"></div>
            <div style="display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2; width: 100%;">
                <div id="u_indicator_step1" class="u-step-indicator active" style="background: #881337; color: white; padding: 6px 14px; border-radius: 9999px; font-size: 11.5px; font-weight: 800; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <span style="background: rgba(255,255,255,0.2); width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">1</span>
                    <span>البيانات الشخصية والحساب</span>
                </div>
                <div id="u_indicator_step2" class="u-step-indicator" style="background: #f1f5f9; color: #64748b; padding: 6px 14px; border-radius: 9999px; font-size: 11.5px; font-weight: 800; display: flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1;">
                    <span style="background: #cbd5e1; color: #334155; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">2</span>
                    <span>البيانات المهنية والتسكين</span>
                </div>
                <div id="u_indicator_step3" class="u-step-indicator" style="background: #f1f5f9; color: #64748b; padding: 6px 14px; border-radius: 9999px; font-size: 11.5px; font-weight: 800; display: flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1;">
                    <span style="background: #cbd5e1; color: #334155; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">3</span>
                    <span>الرتبة ونطاق الصلاحيات</span>
                </div>
                <div id="u_indicator_step4" class="u-step-indicator" style="background: #f1f5f9; color: #64748b; padding: 6px 14px; border-radius: 9999px; font-size: 11.5px; font-weight: 800; display: flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1;">
                    <span style="background: #cbd5e1; color: #334155; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">4</span>
                    <span>المراجعة والتزامن</span>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <form id="eess-unified-user-form" enctype="multipart/form-data" style="padding: 24px; flex: 1; overflow-y: auto;" onsubmit="return false;">
            <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
            <input type="hidden" name="user_id" id="u_user_id" value="0">
            <input type="hidden" name="form_mode" id="u_form_mode" value="add_user">

            <!-- STEP 1: PERSONAL & ACCOUNT INFORMATION -->
            <div id="u_step_1_container">
                <!-- Centered Professional Profile Photo Section -->
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 20px; margin-bottom: 20px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div style="width: 100px; height: 100px; border-radius: 12px; border: 2px solid #cbd5e1; background: #ffffff; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                        <img id="u_photo_preview" src="" alt="صورة الموظف" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%2394a3b8\'><path d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/></svg>'">
                    </div>
                    <input type="file" name="profile_photo" id="u_profile_photo" accept="image/*" style="font-size: 12px; margin-bottom: 6px;" onchange="eessPreviewAvatar(this)">
                    <p style="margin: 0; font-size: 12px; font-weight: 700; color: #881337;">يرجى رفع صورة شخصية مربعة بخلفية بيضاء.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <!-- First Name & Last Name -->
                    <div class="eess-float-group" style="position: relative;">
                        <input type="text" name="first_name" id="u_first_name" class="sm-input eess-float-input" placeholder=" " required style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;" oninput="eessValidateField(this)">
                        <label class="eess-float-label">الاسم الأول *</label>
                        <span class="eess-field-error" id="err_u_first_name" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى إدخال الاسم الأول.</span>
                    </div>

                    <div class="eess-float-group" style="position: relative;">
                        <input type="text" name="last_name" id="u_last_name" class="sm-input eess-float-input" placeholder=" " required style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;" oninput="eessValidateField(this)">
                        <label class="eess-float-label">اسم العائلة / اللقب *</label>
                        <span class="eess-field-error" id="err_u_last_name" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى إدخال اسم العائلة.</span>
                    </div>

                    <!-- Employee ID (Username) & Email -->
                    <div class="eess-float-group" style="position: relative;">
                        <input type="text" name="employee_id" id="u_employee_id" class="sm-input eess-float-input" placeholder=" " required style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px; font-weight: bold; direction: ltr; text-align: right;" oninput="eessSyncUsername(this)" onblur="eessCheckUniqueness('employee_id')">
                        <input type="hidden" name="username" id="u_username">
                        <label class="eess-float-label">رقم الموظف الوظيفي / اسم المستخدم *</label>
                        <span class="eess-field-error" id="err_u_employee_id" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى إدخال الرقم الوظيفي للموظف.</span>
                    </div>

                    <div class="eess-float-group" style="position: relative;">
                        <input type="email" name="user_email" id="u_user_email" class="sm-input eess-float-input" placeholder=" " required style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px; direction: ltr; text-align: right;" onblur="eessCheckUniqueness('email')" oninput="eessValidateField(this)">
                        <label class="eess-float-label">البريد الإلكتروني الرسمي *</label>
                        <span class="eess-field-error" id="err_u_user_email" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى إدخال بريد إلكتروني صحيح.</span>
                    </div>

                    <!-- Country Code & Phone Number -->
                    <div class="eess-float-group" style="position: relative; grid-column: span 2;">
                        <div style="display: flex; gap: 8px; direction: ltr;">
                            <select name="country_code" id="u_country_code" class="sm-select" style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 12.5px; width: 130px; font-weight: bold;">
                                <option value="+971">🇦🇪 +971 (الإمارات)</option>
                                <option value="+966">🇸🇦 +966 (السعودية)</option>
                                <option value="+965">🇰🇼 +965 (الكويت)</option>
                                <option value="+974">🇶🇦 +974 (قطر)</option>
                                <option value="+973">🇧🇭 +973 (البحرين)</option>
                                <option value="+968">🇴🇲 +968 (عمان)</option>
                                <option value="+20">🇪🇬 +20 (مصر)</option>
                            </select>
                            <input type="text" name="phone_number" id="u_phone_number" class="sm-input eess-float-input" placeholder=" " required style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px; flex: 1; text-align: left;" oninput="eessValidateField(this)">
                        </div>
                        <label class="eess-float-label" style="right: 140px;">رقم الجوال / الهاتف *</label>
                        <span class="eess-field-error" id="err_u_phone_number" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى إدخال رقم الهاتف.</span>
                    </div>

                    <!-- Date of Birth & Nationality -->
                    <div class="eess-float-group" style="position: relative;">
                        <input type="date" name="dob" id="u_dob" class="sm-input eess-float-input" placeholder=" " style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 12.5px;">
                        <label class="eess-float-label">تاريخ الميلاد</label>
                    </div>

                    <div class="eess-float-group" style="position: relative;">
                        <input type="text" name="nationality" id="u_nationality" class="sm-input eess-float-input" placeholder=" " style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                        <label class="eess-float-label">الجنسية</label>
                    </div>

                    <!-- Civil ID & Account Status -->
                    <div class="eess-float-group" style="position: relative;">
                        <input type="text" name="civil_id" id="u_civil_id" class="sm-input eess-float-input" placeholder=" " style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                        <label class="eess-float-label">الرقم المدني / الهوية</label>
                    </div>

                    <div class="eess-float-group" style="position: relative;">
                        <select name="user_status" id="u_user_status" class="sm-select eess-float-input" style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                            <option value="active">مفعل (نشط)</option>
                            <option value="pending">معلق (قيد المراجعة)</option>
                            <option value="restricted">مقيد / محظور</option>
                        </select>
                        <label class="eess-float-label">حالة الحساب</label>
                    </div>
                </div>

                <!-- Passwords Row (Conditional in Edit Mode) -->
                <div id="u_password_row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <div class="eess-float-group" style="position: relative;">
                        <input type="password" name="user_pass" id="u_user_pass" class="sm-input eess-float-input" placeholder=" " style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;" oninput="eessValidateField(this)">
                        <label class="eess-float-label">كلمة المرور *</label>
                        <span class="eess-field-error" id="err_u_user_pass" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">كلمة المرور يجب أن لا تقل عن 6 خانات.</span>
                    </div>
                    <div class="eess-float-group" style="position: relative;">
                        <input type="password" name="user_pass_confirm" id="u_user_pass_confirm" class="sm-input eess-float-input" placeholder=" " style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;" oninput="eessValidateField(this)">
                        <label class="eess-float-label">تأكيد كلمة المرور *</label>
                        <span class="eess-field-error" id="err_u_user_pass_confirm" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">كلمتا المرور غير متطابقتين.</span>
                    </div>
                </div>

                <!-- Toggle Password Edit Button (Edit Mode Only) -->
                <div id="u_change_pass_toggle_container" style="display:none; margin-bottom: 16px;">
                    <button type="button" id="u_change_pass_btn" onclick="eessToggleChangePassword()" class="sm-btn sm-btn-outline" style="height: 34px; padding: 0 16px; font-size: 12px; color: #475569; border-radius: 9999px;">
                        <span class="dashicons dashicons-lock" style="font-size: 14px; width: 14px; height: 14px; margin-left: 4px;"></span>
                        <span>تغيير كلمة المرور لهذا الحساب</span>
                    </button>
                </div>
            </div>

            <!-- STEP 2: PROFESSIONAL INFORMATION & ASSIGNMENTS -->
            <div id="u_step_2_container" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 14px 0; font-size: 13.5px; font-weight: 800; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">البيانات المؤسسية والتبعية التنظيمية</h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <!-- Institution Selection -->
                        <div class="eess-float-group" style="position: relative;">
                            <select name="institution_id" id="u_institution_id" class="sm-select eess-float-input" onchange="eessOnInstitutionChanged()" required style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                                <option value="">-- اختر الجهة --</option>
                                <?php foreach ($institutions as $inst): ?>
                                    <option value="<?php echo esc_attr($inst->id); ?>"><?php echo esc_html($inst->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="eess-float-label">الجهة / المؤسسة الرئيسية *</label>
                            <span class="eess-field-error" id="err_u_institution_id" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى اختيار الجهة المؤسسية.</span>
                        </div>

                        <!-- School / Branch Selection -->
                        <div class="eess-float-group" id="u_school_wrapper" style="position: relative;">
                            <select name="school_id" id="u_school_id" class="sm-select eess-float-input" onchange="eessOnSchoolChanged()" style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                                <option value="">-- اختر المدرسة --</option>
                                <?php foreach ($all_schools as $sch): ?>
                                    <option value="<?php echo esc_attr($sch->id); ?>" data-institution="<?php echo esc_attr($sch->institution_id); ?>"><?php echo esc_html($sch->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="eess-float-label">المدرسة / الفرع *</label>
                            <span class="eess-field-error" id="err_u_school_id" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى تحديد المدرسة.</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                        <!-- Department -->
                        <div class="eess-float-group" id="u_department_wrapper" style="position: relative;">
                            <select name="department" id="u_department" class="sm-select eess-float-input" style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                                <option value="">-- اختر القسم --</option>
                                <?php foreach ($departments as $dept_key => $dept_lbl): ?>
                                    <option value="<?php echo esc_attr($dept_lbl); ?>"><?php echo esc_html($dept_lbl); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="eess-float-label">القسم / الإدارة</label>
                        </div>

                        <!-- Subject / Specialization -->
                        <div class="eess-float-group" id="u_subject_wrapper" style="position: relative;">
                            <select name="specialization" id="u_specialization" class="sm-select eess-float-input" style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                                <option value="">-- اختر التخصص --</option>
                                <?php foreach ($subjects as $subj_code => $subj_name): ?>
                                    <option value="<?php echo esc_attr($subj_name); ?>"><?php echo esc_html($subj_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="eess-float-label">المادة / التخصص *</label>
                            <span class="eess-field-error" id="err_u_specialization" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى اختيار المادة للتخصص.</span>
                        </div>

                        <!-- Job Title Override -->
                        <div class="eess-float-group" style="position: relative;">
                            <input type="text" name="official_title" id="u_official_title" class="sm-input eess-float-input" placeholder=" " style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;">
                            <label class="eess-float-label">المسمى الوظيفي الرسمى</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: ROLES & OPERATIONAL SCOPE -->
            <div id="u_step_3_container" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 14px 0; font-size: 13.5px; font-weight: 800; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">تحديد الرتبة والنطاق التشغيلي</h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Role Selection -->
                        <div class="eess-float-group" style="position: relative;">
                            <select name="user_role" id="u_user_role" class="sm-select eess-float-input" onchange="eessOnRoleChanged()" required style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px; font-weight: bold;">
                                <option value="">-- اختر الرتبة الوظيفية --</option>
                                <option value="sm_teacher">معلم / عضو هيئة تدريس</option>
                                <option value="sm_hod">رئيس قسم (HOD)</option>
                                <option value="sm_principal">مدير مدرسة / القائد التربوي</option>
                                <option value="sm_supervisor">موجه / مشرف تربوي</option>
                                <option value="sm_activities_supervisor">مشرف أنشطة وفعاليات</option>
                                <option value="sm_academic_advisor">المرشد الأكاديمي / الموجه الطلابي</option>
                                <option value="sm_clinic">طبيب / زائر صحي للمدرسة</option>
                                <option value="sm_accountant">محاسب / مسؤول مالي</option>
                                <option value="administrator">مدير النظام (System Administrator)</option>
                            </select>
                            <label class="eess-float-label">الرتبة الوظيفية *</label>
                            <span class="eess-field-error" id="err_u_user_role" style="display:none; color:#dc2626; font-size:11px; font-weight:bold; margin-top:2px;">يرجى تحديد الرتبة الوظيفية.</span>
                        </div>

                        <!-- Access Scope -->
                        <div class="eess-float-group" style="position: relative;">
                            <select name="access_scope" id="u_access_scope" class="sm-select eess-float-input" style="height: 42px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 13px;" onchange="eessOnScopeChanged()">
                                <option value="institution">الجهة المؤسسية بالكامل وكافة فروعها</option>
                                <option value="school">المدرسة / الفرع المحدد فقط</option>
                                <option value="multiple_schools">مدارس متعددة مختارة</option>
                            </select>
                            <label class="eess-float-label">نطاق الصلاحيات *</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: REVIEW & SYNCHRONIZATION -->
            <div id="u_step_4_container" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
                    <h4 style="margin: 0 0 14px 0; font-size: 14px; font-weight: 800; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">ملخص ومراجعة بيانات الحساب والتسجيل</h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 13px; color: #334155;">
                        <div><strong>الاسم الكامل:</strong> <span id="rev_u_fullname">-</span></div>
                        <div><strong>رقم الموظف / اسم المستخدم:</strong> <span id="rev_u_empid" style="font-family: monospace; font-weight: bold; color: #881337;">-</span></div>
                        <div><strong>البريد الإلكتروني:</strong> <span id="rev_u_email" style="font-family: monospace;">-</span></div>
                        <div><strong>رقم الجوال:</strong> <span id="rev_u_phone" style="font-family: monospace;">-</span></div>
                        <div><strong>تاريخ الميلاد:</strong> <span id="rev_u_dob">-</span></div>
                        <div><strong>الجنسية:</strong> <span id="rev_u_nationality">-</span></div>
                        <div><strong>الجهة والمدرسة:</strong> <span id="rev_u_school">-</span></div>
                        <div><strong>الرتبة والتخصص:</strong> <span id="rev_u_role_subj" style="color: #0284c7; font-weight: 800;">-</span></div>
                    </div>

                    <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #15803d; padding: 12px 16px; border-radius: 10px; margin-top: 16px; font-size: 12.5px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-yes-alt" style="font-size: 18px; width: 18px; height: 18px;"></span>
                        <span>جميع البيانات مكتملة وجاهزة للحفظ والتزامن المباشر مع المنصة الرقمية.</span>
                    </div>
                </div>
            </div>

            <!-- Footer Navigation Controls (Clean Buttons without emoji) -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 20px;">
                <button type="button" onclick="eessCloseUnifiedUserModal()" class="sm-btn sm-btn-outline" style="height: 40px; padding: 0 20px; font-size: 13px; color: #64748b; border-radius: 9999px !important;">إلغاء</button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" id="u_btn_prev" onclick="eessGoToStep(eessCurrentStep - 1)" class="sm-btn sm-btn-outline" style="height: 40px; padding: 0 22px; font-size: 13px; border-radius: 9999px !important; display: none;">السابق</button>
                    <button type="button" id="u_btn_next" onclick="eessGoToStep(eessCurrentStep + 1)" class="sm-btn" style="height: 40px; padding: 0 24px; font-size: 13px; font-weight: 800; background: #881337; color: white !important; border: none; border-radius: 9999px !important; cursor: pointer;">التالي</button>
                    <button type="button" id="u_btn_save" onclick="eessSubmitUnifiedUserForm()" class="sm-btn" style="height: 40px; padding: 0 28px; font-size: 13px; font-weight: 800; background: #000000; color: white !important; border: none; border-radius: 9999px !important; cursor: pointer; display: none;">حفظ وتزامن البيانات</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.eess-float-group {
    position: relative;
}
.eess-float-input {
    width: 100%;
    box-sizing: border-box;
    transition: all 0.2s ease;
}
.eess-float-label {
    position: absolute;
    top: -9px;
    right: 12px;
    background: #ffffff;
    padding: 0 6px;
    font-size: 11px;
    font-weight: 800;
    color: #475569;
    pointer-events: none;
    border-radius: 4px;
}
</style>

<script src="<?php echo SM_PLUGIN_URL . 'public/js/unified-user-modal.js'; ?>"></script>
