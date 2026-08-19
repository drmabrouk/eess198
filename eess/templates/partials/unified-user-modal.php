<?php
if (!defined('ABSPATH')) exit;

$institutions = class_exists('EESS_Org_Helper') ? EESS_Org_Helper::get_institutions() : array();
$all_schools  = class_exists('EESS_Org_Helper') ? EESS_Org_Helper::get_all_schools() : array();
$subjects     = class_exists('SM_Settings') ? SM_Settings::get_subjects() : array();
$departments  = class_exists('SM_Settings') ? SM_Settings::get_departments() : array();
?>

<!-- UNIFIED USER & EMPLOYEE MANAGEMENT MODAL -->
<div id="unified-user-modal" class="sm-modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 999999; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); position: fixed; inset: 0; padding: 15px;">
    <div class="sm-modal-content" style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 780px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; font-family: 'Cairo', sans-serif;" dir="rtl">

        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; border-top-left-radius: 14px; border-top-right-radius: 14px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-admin-users" style="font-size: 22px; width: 22px; height: 22px; color: var(--sm-primary-color, #1e293b);"></span>
                <h3 id="u_modal_title" style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">إدارة وتعديل حساب الموظف</h3>
            </div>
            <button type="button" class="sm-modal-close" onclick="eessCloseUnifiedUserModal()" style="background: transparent; border: none; font-size: 22px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Step Indicator Bar -->
        <div style="padding: 12px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; justify-content: space-between; max-width: 500px; margin: 0 auto; position: relative;">
                <div style="position: absolute; top: 50%; left: 10%; right: 10%; height: 2px; background: #e2e8f0; z-index: 1; transform: translateY(-50%);"></div>
                <div id="u_indicator_step1" class="u-step-indicator active" style="position: relative; z-index: 2; background: var(--sm-primary-color, #1e293b); color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <span style="background: rgba(255,255,255,0.2); width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">1</span>
                    <span>البيانات الأساسية والحساب</span>
                </div>
                <div id="u_indicator_step2" class="u-step-indicator" style="position: relative; z-index: 2; background: #f1f5f9; color: #64748b; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1;">
                    <span style="background: #cbd5e1; color: #334155; width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">2</span>
                    <span>الرتبة والصلاحيات والجهة</span>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <form id="eess-unified-user-form" enctype="multipart/form-data" style="padding: 16px 20px;" onsubmit="return false;">
            <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
            <input type="hidden" name="user_id" id="u_user_id" value="0">
            <input type="hidden" name="form_mode" id="u_form_mode" value="add_user">

            <!-- STEP 1: BASIC INFORMATION & ACCOUNT -->
            <div id="u_step_1_container">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <!-- First Name & Last Name -->
                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">الاسم الأول <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="first_name" id="u_first_name" class="sm-input" placeholder="مثال: محمد" required style="height: 34px; font-size: 12px;" oninput="eessValidateField(this)">
                        <span class="eess-field-error" id="err_u_first_name" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى إدخال الاسم الأول.</span>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">اسم العائلة / اللقب <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="last_name" id="u_last_name" class="sm-input" placeholder="مثال: أحمد" required style="height: 34px; font-size: 12px;" oninput="eessValidateField(this)">
                        <span class="eess-field-error" id="err_u_last_name" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى إدخال اسم العائلة.</span>
                    </div>

                    <!-- Employee ID (Username) & Email -->
                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">رقم الموظف الوظيفي / اسم المستخدم <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="employee_id" id="u_employee_id" class="sm-input" placeholder="مثال: 00025" required style="height: 34px; font-size: 12px; font-weight: bold; direction: ltr; text-align: right;" oninput="eessSyncUsername(this)" onblur="eessCheckUniqueness('employee_id')">
                        <input type="hidden" name="username" id="u_username">
                        <span class="eess-field-error" id="err_u_employee_id" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى إدخال الرقم الوظيفي للموظف بدون بادئة.</span>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">البريد الإلكتروني الرسمي <span style="color:#ef4444;">*</span></label>
                        <input type="email" name="user_email" id="u_user_email" class="sm-input" placeholder="m.ahmed@eess.online" required style="height: 34px; font-size: 12px; direction: ltr; text-align: right;" onblur="eessCheckUniqueness('email')" oninput="eessValidateField(this)">
                        <span class="eess-field-error" id="err_u_user_email" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى إدخال بريد إلكتروني صحيح ومستخدم.</span>
                    </div>

                    <!-- Country Code & Phone Number -->
                    <div class="sm-form-group" style="grid-column: span 2;">
                        <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">رمز الدولة ورقم الهاتف / الجوال <span style="color:#ef4444;">*</span></label>
                        <div style="display: flex; gap: 8px; direction: ltr;">
                            <select name="country_code" id="u_country_code" class="sm-select" style="height: 34px; font-size: 12px; width: 130px; font-weight: bold;">
                                <option value="+971">🇦🇪 +971 (الإمارات)</option>
                                <option value="+966">🇸🇦 +966 (السعودية)</option>
                                <option value="+965">🇰🇼 +965 (الكويت)</option>
                                <option value="+974">🇶🇦 +974 (قطر)</option>
                                <option value="+973">🇧🇭 +973 (البحرين)</option>
                                <option value="+968">🇴🇲 +968 (عمان)</option>
                                <option value="+20">🇪🇬 +20 (مصر)</option>
                            </select>
                            <input type="text" name="phone_number" id="u_phone_number" class="sm-input" placeholder="501234567" required style="height: 34px; font-size: 12px; flex: 1; text-align: left;" oninput="eessValidateField(this)">
                        </div>
                        <span class="eess-field-error" id="err_u_phone_number" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى إدخال رقم الهاتف.</span>
                    </div>
                </div>

                <!-- Passwords Row (Conditional in Edit Mode) -->
                <div id="u_password_row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-bottom: 12px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px dashed #cbd5e1;">
                    <div class="sm-form-group" style="margin-bottom:0;">
                        <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">كلمة المرور <span id="u_pass_req" style="color:#ef4444;">*</span></label>
                        <input type="password" name="user_pass" id="u_user_pass" class="sm-input" placeholder="••••••••" style="height: 34px; font-size: 12px;" oninput="eessValidateField(this)">
                        <span class="eess-field-error" id="err_u_user_pass" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">كلمة المرور يجب أن لا تقل عن 6 خانات.</span>
                    </div>
                    <div class="sm-form-group" style="margin-bottom:0;">
                        <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">تأكيد كلمة المرور <span id="u_pass_confirm_req" style="color:#ef4444;">*</span></label>
                        <input type="password" name="user_pass_confirm" id="u_user_pass_confirm" class="sm-input" placeholder="••••••••" style="height: 34px; font-size: 12px;" oninput="eessValidateField(this)">
                        <span class="eess-field-error" id="err_u_user_pass_confirm" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">كلمتا المرور غير متطابقتين.</span>
                    </div>
                </div>

                <!-- Toggle Password Edit Button (Edit Mode Only) -->
                <div id="u_change_pass_toggle_container" style="display:none; margin-bottom: 12px;">
                    <button type="button" id="u_change_pass_btn" onclick="eessToggleChangePassword()" class="sm-btn sm-btn-outline" style="height: 28px; padding: 0 12px; font-size: 11px; color: #475569; border-radius: 6px;">
                        <span class="dashicons dashicons-lock" style="font-size: 14px; width: 14px; height: 14px; margin-left: 4px;"></span>
                        <span>تغيير كلمة المرور لهذا الحساب</span>
                    </button>
                </div>

                <!-- Profile Photo & Status Row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; align-items: center; background: #fff; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 8px;">
                    <!-- Avatar Preview Container -->
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; aspect-ratio: 1/1 !important; border-radius: 50% !important; overflow: hidden !important; border: 2px solid #cbd5e1; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <img id="u_photo_preview" src="" alt="صورة الموظف" style="width: 100% !important; height: 100% !important; object-fit: cover !important; aspect-ratio: 1/1 !important; border-radius: 50% !important; display: block;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%2394a3b8\'><path d=\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\'/></svg>'">
                        </div>
                        <div>
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 2px; display: block;">الصورة الشخصية للموظف</label>
                            <input type="file" name="profile_photo" id="u_profile_photo" accept="image/*" style="font-size: 11px;" onchange="eessPreviewAvatar(this)">
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">يتم ضبط القياس تلقائياً بدقة دائرية منتظمة.</div>
                        </div>
                    </div>

                    <!-- Account Status & Civil ID -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">حالة الحساب</label>
                            <select name="user_status" id="u_user_status" class="sm-select" style="height: 34px; font-size: 12px;">
                                <option value="active">مفعل (نشط)</option>
                                <option value="pending">معلق (قيد المراجعة)</option>
                                <option value="restricted">مقيد / محظور</option>
                            </select>
                        </div>
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">الرقم المدني / الهوية</label>
                            <input type="text" name="civil_id" id="u_civil_id" class="sm-input" placeholder="1029384756" style="height: 34px; font-size: 12px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: ROLE, ORGANIZATION & ACCESS -->
            <div id="u_step_2_container" style="display: none;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 12px; font-weight: 800; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">تحديد الرتبة ونطاق الصلاحيات التشغيلية</h4>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-bottom: 10px;">
                        <!-- Role Selection -->
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">رتبة ووظيفة الموظف <span style="color:#ef4444;">*</span></label>
                            <select name="user_role" id="u_user_role" class="sm-select" onchange="eessOnRoleChanged()" required style="height: 34px; font-size: 12px; font-weight: bold;">
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
                            <span class="eess-field-error" id="err_u_user_role" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى تحديد الرتبة الوظيفية.</span>
                        </div>

                        <!-- Access Scope -->
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">نطاق الصلاحيات (Access Scope) <span style="color:#ef4444;">*</span></label>
                            <select name="access_scope" id="u_access_scope" class="sm-select" style="height: 34px; font-size: 12px;" onchange="eessOnScopeChanged()">
                                <option value="institution">الجهة المؤسسية بالكامل وكافة فروعها</option>
                                <option value="school">المدرسة / الفرع المحدد فقط</option>
                                <option value="multiple_schools">مدارس متعددة مختارة</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Organization Dynamic Hierarchy -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 12px; font-weight: 800; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">التبعية التنظيمية والفرع والقسم</h4>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-bottom: 10px;">
                        <!-- Institution Selection -->
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">الجهة / المؤسسة الرئيسية <span style="color:#ef4444;">*</span></label>
                            <select name="institution_id" id="u_institution_id" class="sm-select" onchange="eessOnInstitutionChanged()" required style="height: 34px; font-size: 12px;">
                                <option value="">-- اختر الجهة التابع لها --</option>
                                <?php foreach ($institutions as $inst): ?>
                                    <option value="<?php echo esc_attr($inst->id); ?>"><?php echo esc_html($inst->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="eess-field-error" id="err_u_institution_id" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى اختيار الجهة المؤسسية.</span>
                        </div>

                        <!-- School / Branch Selection -->
                        <div class="sm-form-group" id="u_school_wrapper" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">المدرسة / الفرع الرئيسي <span style="color:#ef4444;">*</span></label>
                            <select name="school_id" id="u_school_id" class="sm-select" onchange="eessOnSchoolChanged()" style="height: 34px; font-size: 12px;">
                                <option value="">-- اختر المدرسة --</option>
                                <?php foreach ($all_schools as $sch): ?>
                                    <option value="<?php echo esc_attr($sch->id); ?>" data-institution="<?php echo esc_attr($sch->institution_id); ?>"><?php echo esc_html($sch->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="eess-field-error" id="err_u_school_id" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى تحديد المدرسة.</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                        <!-- Department -->
                        <div class="sm-form-group" id="u_department_wrapper" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">القسم / الإدارة التابع لها</label>
                            <select name="department" id="u_department" class="sm-select" style="height: 34px; font-size: 12px;">
                                <option value="">-- اختر القسم --</option>
                                <?php foreach ($departments as $dept_key => $dept_lbl): ?>
                                    <option value="<?php echo esc_attr($dept_lbl); ?>"><?php echo esc_html($dept_lbl); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Subject / Specialization (Relevant for Teachers / HOD) -->
                        <div class="sm-form-group" id="u_subject_wrapper" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">المادة / التخصص الأكاديمي <span style="color:#ef4444;">*</span></label>
                            <select name="specialization" id="u_specialization" class="sm-select" style="height: 34px; font-size: 12px;">
                                <option value="">-- اختر التخصص --</option>
                                <?php foreach ($subjects as $subj_code => $subj_name): ?>
                                    <option value="<?php echo esc_attr($subj_name); ?>"><?php echo esc_html($subj_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="eess-field-error" id="err_u_specialization" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:2px;">يرجى اختيار المادة للتخصص.</span>
                        </div>

                        <!-- Job Title Override -->
                        <div class="sm-form-group" style="margin-bottom:0;">
                            <label class="sm-label" style="font-size: 11px; font-weight: 700; color: #334155;">المسمى الوظيفي الرسمي</label>
                            <input type="text" name="official_title" id="u_official_title" class="sm-input" placeholder="مثال: معلم أول تربية بدنية" style="height: 34px; font-size: 12px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Navigation Controls -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 10px;">
                <button type="button" onclick="eessCloseUnifiedUserModal()" class="sm-btn sm-btn-outline" style="height: 36px; padding: 0 16px; font-size: 12px; color: #64748b;">إلغاء</button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="u_btn_prev" onclick="eessGoToStep(1)" class="sm-btn sm-btn-outline" style="height: 36px; padding: 0 18px; font-size: 12px; display: none;">السابق</button>
                    <button type="button" id="u_btn_next" onclick="eessGoToStep(2)" class="sm-btn" style="height: 36px; padding: 0 22px; font-size: 12px; font-weight: 800; background: var(--sm-primary-color, #1e293b); color: white !important;">التالي ➔</button>
                    <button type="button" id="u_btn_save" onclick="eessSubmitUnifiedUserForm()" class="sm-btn" style="height: 36px; padding: 0 24px; font-size: 12px; font-weight: 800; background: #16a34a; color: white !important; display: none;">💾 حفظ وتزامن البيانات</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo SM_PLUGIN_URL . 'public/js/unified-user-modal.js'; ?>"></script>
