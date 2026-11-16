<?php

namespace App\Support;

class DoctorFaqCatalog
{
    /**
     * @return array{
     *     welcome: string,
     *     categories: list<array{id: string, label: string}>,
     *     questions: list<array{id: string, category: string, question: string, answer: string, keywords: list<string>, related: list<string>}>
     * }
     */
    public function toArray(): array
    {
        return [
            'welcome' => 'Hello. I can help you use this health system. Tap a question, and I will answer in simple words.',
            'categories' => [
                ['id' => 'about', 'label' => 'About this'],
                ['id' => 'login', 'label' => 'Sign in'],
                ['id' => 'roles', 'label' => 'Who can do what'],
                ['id' => 'patients', 'label' => 'Patients'],
                ['id' => 'checkups', 'label' => 'Checkups'],
                ['id' => 'immunizations', 'label' => 'Vaccines'],
                ['id' => 'maternal', 'label' => 'Care for mothers'],
                ['id' => 'announcements', 'label' => 'News'],
                ['id' => 'sms', 'label' => 'Text messages'],
                ['id' => 'reports', 'label' => 'Reports'],
                ['id' => 'logs', 'label' => 'Activity logs'],
                ['id' => 'dashboard', 'label' => 'Home screen'],
                ['id' => 'profile', 'label' => 'My account'],
                ['id' => 'pwa', 'label' => 'Add to phone'],
            ],
            'questions' => $this->questions(),
        ];
    }

    /**
     * @return list<array{id: string, category: string, question: string, answer: string, keywords: list<string>, related: list<string>}>
     */
    private function questions(): array
    {
        return [
            $this->question(
                'what-is-the-system',
                'about',
                'What is SanFran HealthSys?',
                "This is the health record of Barangay San Francisco in Talibon, Bohol.\n\nHealth workers use it to save patients, checkups, vaccines, care for mothers, news, text reminders, and reports.\n\nPeople in the barangay can open this page to read health news. You can also save this page on your phone.",
                ['system', 'about', 'barangay', 'san francisco', 'talibon', 'bohol', 'what is'],
                ['who-is-it-for', 'what-can-residents-see', 'what-does-dashboard-show'],
            ),
            $this->question(
                'who-is-it-for',
                'about',
                'Who is this for?',
                "Two kinds of people use it.\n\nPeople in the barangay open this page to read news about vaccines, mothers, health warnings, emergencies, and events.\n\nHealth center staff sign in with a username and password. Staff can be System Admin, Midwife, or Barangay Health Worker.",
                ['who', 'staff', 'residents', 'community', 'users'],
                ['what-are-the-roles', 'how-do-staff-log-in', 'what-can-residents-see'],
            ),
            $this->question(
                'what-can-residents-see',
                'about',
                'What can I see if I am not staff?',
                "You can read health news on this page. You can pick a kind of news, and you can show newest first or oldest first.\n\nYou can add this page to your phone. You can also open Staff Login if you work at the health center.\n\nYou cannot see patient records unless you sign in.",
                ['public', 'landing', 'home', 'residents', 'without login'],
                ['when-does-announcement-appear', 'how-to-install-pwa', 'how-do-staff-log-in'],
            ),
            $this->question(
                'how-do-staff-log-in',
                'login',
                'How do staff sign in?',
                "Tap Staff Login. Type your username and password. Email is not used to sign in.\n\nYou can tick Remember me if you want to stay signed in on that phone or computer.\n\nAfter you sign in, you go to the home screen for staff.",
                ['login', 'sign in', 'username', 'password', 'remember'],
                ['why-cant-i-log-in', 'how-do-i-log-out', 'what-if-account-deactivated'],
            ),
            $this->question(
                'why-cant-i-log-in',
                'login',
                'Why can I not sign in?',
                "Sign in fails if the username or password is wrong, or if the account is turned off.\n\nAsk a System Admin or Midwife to check your account. A Barangay Health Worker cannot fix accounts.",
                ['cannot login', 'failed', 'wrong password', 'locked', 'inactive'],
                ['what-if-account-deactivated', 'who-can-create-users', 'how-do-staff-log-in'],
            ),
            $this->question(
                'how-do-i-log-out',
                'login',
                'How do I sign out?',
                "On staff pages, tap your name or photo at the top right. Then tap Log out.\n\nThe system will ask if you are sure. After that, you go back to the public news page.",
                ['logout', 'sign out', 'session'],
                ['how-do-staff-log-in', 'what-are-activity-logs'],
            ),
            $this->question(
                'what-if-account-deactivated',
                'login',
                'What if an account is turned off?',
                "If an account is turned off, that person cannot sign in.\n\nOnly a System Admin or Midwife can turn an account back on. You cannot turn off your own account.",
                ['deactivate', 'disabled', 'inactive', 'enable'],
                ['who-can-create-users', 'why-cant-i-log-in', 'what-are-the-roles'],
            ),
            $this->question(
                'what-are-the-roles',
                'roles',
                'What are the staff jobs?',
                "There are three staff jobs:\n\n• System Admin — can use everything, including staff accounts, text messages, and activity logs.\n• Midwife — can do clinic work, post news, send texts, and make Barangay Health Worker accounts.\n• Barangay Health Worker — can save patients, checkups, vaccines, and care for mothers, and can open reports. Cannot make staff accounts, send texts, or hide records.",
                ['roles', 'admin', 'midwife', 'bhw', 'permissions'],
                ['what-can-admin-do', 'what-can-midwife-do', 'what-can-bhw-do'],
            ),
            $this->question(
                'what-can-admin-do',
                'roles',
                'What can a System Admin do?',
                "A System Admin can use every part of the staff pages: patients, checkups, vaccines, care for mothers, news, texts, reports, and activity logs.\n\nThey can make Midwife and Barangay Health Worker accounts. They cannot turn off their own account.",
                ['system admin', 'administrator', 'full access'],
                ['what-can-midwife-do', 'who-can-create-users', 'what-are-the-roles'],
            ),
            $this->question(
                'what-can-midwife-do',
                'roles',
                'What can a Midwife do?',
                "A Midwife can save patients, checkups, vaccines, and care for mothers. They can post news, send texts, and open reports.\n\nOn Users, they can only make and edit Barangay Health Worker accounts.",
                ['midwife', 'nurse'],
                ['what-can-bhw-do', 'who-can-create-users', 'who-can-send-sms'],
            ),
            $this->question(
                'what-can-bhw-do',
                'roles',
                'What can a Barangay Health Worker do?',
                "A Barangay Health Worker can open the staff home screen, save patients, record checkups, vaccines, and visits for mothers, read news, and open reports.\n\nThey can hide records and bring them back from Archive. They cannot open Users, text messages, or activity logs. They cannot post news.",
                ['bhw', 'barangay health worker', 'health worker'],
                ['who-can-archive', 'what-are-the-roles', 'how-do-i-register-a-patient'],
            ),
            $this->question(
                'who-can-create-users',
                'roles',
                'Who can make staff accounts?',
                "Only a System Admin or Midwife can open Users.\n\nA System Admin can make Midwife and Barangay Health Worker accounts. A Midwife can make Barangay Health Worker accounts only.\n\nA new account needs a name, username, email, job, and a password with at least 8 letters or numbers.",
                ['create user', 'staff account', 'register staff'],
                ['what-are-the-roles', 'what-if-account-deactivated', 'how-do-staff-log-in'],
            ),
            $this->question(
                'how-do-i-register-a-patient',
                'patients',
                'How do I add a patient?',
                "Sign in, open Patients, then tap the button to add a patient.\n\nYou must enter the name, birth date, if the person is male or female, civil status, and address. Phone and email are optional.\n\nThe system makes a patient code for you. You do not type the code.",
                ['register', 'add patient', 'new patient', 'create patient'],
                ['how-are-patient-codes-created', 'what-patient-fields', 'how-do-i-search-patients'],
            ),
            $this->question(
                'how-are-patient-codes-created',
                'patients',
                'How are patient codes created?',
                "The system makes a code for every patient, like SF-2026-0001.\n\nThe number goes up by itself each year. Staff do not type this code.",
                ['patient code', 'sf-', 'id number', 'unique'],
                ['how-do-i-register-a-patient', 'what-patient-fields', 'how-do-i-search-patients'],
            ),
            $this->question(
                'what-patient-fields',
                'patients',
                'What information is saved for a patient?',
                "Name, birth date, age, if the person is male or female, civil status, phone, email, and address.\n\nYou can also save blood type, allergies, other illnesses, notes, and an emergency contact.\n\nThe record also has the patient code and the staff who added them.",
                ['fields', 'information', 'demographics', 'allergies', 'blood type'],
                ['how-do-i-register-a-patient', 'what-is-on-patient-profile', 'how-do-i-search-patients'],
            ),
            $this->question(
                'how-do-i-search-patients',
                'patients',
                'How do I find a patient?',
                "On Patients, type the patient code, first name, last name, or phone number. You can also show only male or only female.\n\nThe list shows 15 patients at a time, newest first.",
                ['search', 'filter', 'find patient'],
                ['how-do-i-register-a-patient', 'what-is-on-patient-profile', 'how-are-patient-codes-created'],
            ),
            $this->question(
                'what-is-on-patient-profile',
                'patients',
                'What is on a patient page?',
                "When you open a patient, you see their details, checkups, vaccines, care for mothers, and text history.\n\nIf the patient is female, there is also a shortcut for care for mothers.",
                ['profile', 'show', 'tabs', 'patient page'],
                ['what-patient-fields', 'how-does-maternal-care-work', 'who-can-send-sms'],
            ),
            $this->question(
                'who-can-archive',
                'patients',
                'What does Archive mean?',
                "Archive means hide. The record is not shown on the usual list, but it is still saved.\n\nStaff can hide patients, checkups, vaccines, and care for mothers. Open Archive to see those hidden records and restore them.\n\nOnly a System Admin or Midwife can hide news. The system will ask you to confirm first.",
                ['archive', 'delete', 'soft delete', 'remove'],
                ['what-can-bhw-do', 'how-do-i-register-a-patient', 'how-do-announcements-work'],
            ),
            $this->question(
                'how-do-i-record-a-checkup',
                'checkups',
                'How do I record a checkup?',
                "Open Checkups, then tap the button to add a checkup. Choose the patient and the visit date.\n\nYou can type the complaint, diagnosis, treatment, medicine, notes, and a follow-up date if needed.\n\nIf you leave the staff name blank, your own name is saved.",
                ['checkup', 'consultation', 'visit', 'diagnosis'],
                ['what-is-stored-in-checkup', 'who-can-archive', 'what-does-dashboard-show'],
            ),
            $this->question(
                'what-is-stored-in-checkup',
                'checkups',
                'What is saved in a checkup?',
                "The patient, visit date, complaint, diagnosis, treatment, medicine, notes, staff name, and follow-up date if there is one.\n\nOn Checkups, you can search by patient name or code, and pick a date range.",
                ['checkup fields', 'complaint', 'prescription', 'follow-up'],
                ['how-do-i-record-a-checkup', 'how-do-i-search-patients', 'what-reports-exist'],
            ),
            $this->question(
                'how-do-immunizations-work',
                'immunizations',
                'How do vaccines work in this system?',
                "Open Immunizations, then add a record. Choose the patient, vaccine name, date, and next schedule if there is one.\n\nYou can search by vaccine name or patient name. All staff can add, edit, and hide a record. Hidden records can be restored from Archive.",
                ['vaccine', 'immunization', 'dose', 'vaccination'],
                ['what-do-immunization-statuses-mean', 'who-can-archive', 'what-does-dashboard-show'],
            ),
            $this->question(
                'what-do-immunization-statuses-mean',
                'immunizations',
                'What do Completed, Scheduled, and Overdue mean?',
                "• Completed — the vaccine date is today or already done.\n• Scheduled — the next date is today or still coming.\n• Overdue — the next date already passed.\n\nThe staff home screen uses these to show vaccine counts.",
                ['completed', 'scheduled', 'overdue', 'next schedule', 'coverage'],
                ['how-do-immunizations-work', 'what-does-dashboard-show', 'what-reports-exist'],
            ),
            $this->question(
                'how-does-maternal-care-work',
                'maternal',
                'How does care for mothers work?',
                "Open Maternal Care and add a visit. You can only pick a female patient.\n\nEach visit needs which pregnancy this is (1 or more) and the visit date. You can also save the expected birth date, weight, blood pressure, findings, and advice.",
                ['maternal', 'prenatal', 'pregnancy', 'edd', 'pregnant'],
                ['who-can-have-maternal-record', 'what-is-recorded-maternal', 'who-can-archive'],
            ),
            $this->question(
                'who-can-have-maternal-record',
                'maternal',
                'Who can have a maternal care record?',
                "Only a patient saved as female.\n\nIf the mother is not in Patients yet, add her there first.",
                ['female', 'mother', 'pregnant', 'who'],
                ['how-does-maternal-care-work', 'how-do-i-register-a-patient', 'what-is-recorded-maternal'],
            ),
            $this->question(
                'what-is-recorded-maternal',
                'maternal',
                'What is saved in a maternal visit?',
                "Which pregnancy this is, visit date, expected birth date, weight, blood pressure, findings, advice, and notes.\n\nIt is linked to the patient and the staff who saved it.",
                ['weight', 'blood pressure', 'findings', 'edd', 'pregnancy number'],
                ['how-does-maternal-care-work', 'who-can-have-maternal-record', 'what-reports-exist'],
            ),
            $this->question(
                'how-do-announcements-work',
                'announcements',
                'How does news work?',
                "Staff can post news from Announcements. People in the barangay see it on this public page.\n\nA news post has a title, the message, a type, an optional photo, and dates for when it should show.\n\nOnly a System Admin or Midwife can post or hide news. A Barangay Health Worker can read it but cannot post.",
                ['announcement', 'post', 'news', 'advisory'],
                ['what-announcement-types-exist', 'when-does-announcement-appear', 'what-can-residents-see'],
            ),
            $this->question(
                'what-announcement-types-exist',
                'announcements',
                'What kinds of news are there?',
                "The kinds are:\n\n1. Emergency\n2. Health Advisory\n3. Event\n4. Immunization\n5. Maternal Care\n6. General\n\nThe public page groups news by these kinds.",
                ['type', 'emergency', 'advisory', 'event', 'general'],
                ['how-do-announcements-work', 'when-does-announcement-appear', 'what-can-residents-see'],
            ),
            $this->question(
                'when-does-announcement-appear',
                'announcements',
                'When does news show on this page?',
                "People see it only if it is Active, the start date is today or already passed, and it is not yet expired.\n\nIf the start date is still in the future, only staff can see it. If it is turned off, the public cannot see it.",
                ['publish', 'expiry', 'scheduled', 'expired', 'active', 'visible'],
                ['how-do-announcements-work', 'what-can-residents-see', 'what-announcement-types-exist'],
            ),
            $this->question(
                'who-can-send-sms',
                'sms',
                'Who can send a text, and how?',
                "Only System Admins and Midwives can send a text. A Barangay Health Worker cannot.\n\nType the message and the mobile number. You can also pick a patient and the kind of message.\n\nAfter you send it, the system shows if it is still sending, already sent, or failed.",
                ['sms', 'text', 'message', 'clicksend'],
                ['what-sms-types-exist', 'how-are-numbers-formatted', 'what-do-sms-statuses-mean'],
            ),
            $this->question(
                'what-sms-types-exist',
                'sms',
                'What kinds of texts can be sent?',
                "You can send:\n\n• Checkup Reminder\n• Immunization Reminder\n• Maternal Care Reminder\n• Health Advisory\n• Announcement\n• Custom Message\n\nThese are sent when staff tap send. The system does not send them by itself.",
                ['reminder', 'advisory', 'custom', 'notification type'],
                ['who-can-send-sms', 'what-do-sms-statuses-mean', 'how-are-numbers-formatted'],
            ),
            $this->question(
                'how-are-numbers-formatted',
                'sms',
                'How should I type a mobile number?',
                "Type the number the way people usually write it, like 0917....\n\nThe system will save it the right way. Keep the message short so it can fit in one text.",
                ['mobile', 'phone', '63', 'format', 'character limit'],
                ['who-can-send-sms', 'what-do-sms-statuses-mean', 'what-sms-types-exist'],
            ),
            $this->question(
                'what-do-sms-statuses-mean',
                'sms',
                'What do Pending, Sent, and Failed mean?',
                "• Pending — the text is still being sent.\n• Sent — the text went out.\n• Failed — the text did not go out. Try again or check the number.\n\nYou can search texts and pick a date. The patient page also shows texts for that person.",
                ['pending', 'sent', 'failed', 'delivery'],
                ['who-can-send-sms', 'what-sms-types-exist', 'what-reports-exist'],
            ),
            $this->question(
                'what-reports-exist',
                'reports',
                'What reports can I open?',
                "There are reports for Patients, Checkups, Vaccines, Care for mothers, and Texts.\n\nWhen you open Reports, this month’s counts are ready. You do not fill a form.\n\nAll staff can open Reports. You can look at them, print them, or download them as PDF or Excel.",
                ['report', 'pdf', 'excel', 'monthly'],
                ['how-are-reports-generated', 'can-i-download-reports', 'what-does-dashboard-show'],
            ),
            $this->question(
                'how-are-reports-generated',
                'reports',
                'How are monthly reports made?',
                "When you open Reports, the system makes or updates this month’s reports.\n\nIt counts patients added this month, checkups this month, vaccines this month, mother visits this month, and texts this month.",
                ['generate', 'monthly', 'automatic', 'period'],
                ['what-reports-exist', 'can-i-download-reports', 'what-does-dashboard-show'],
            ),
            $this->question(
                'can-i-download-reports',
                'reports',
                'Can I print or download reports?',
                "Yes. You can open a preview, print it, download a PDF, or download Excel.\n\nThe file has the names and dates for that kind of report.",
                ['download', 'print', 'preview', 'pdf', 'excel', 'xlsx'],
                ['what-reports-exist', 'how-are-reports-generated'],
            ),
            $this->question(
                'what-are-activity-logs',
                'logs',
                'What are activity logs?',
                "Activity logs show who did something, what they did, and when.\n\nThis includes sign in, sign out, adding a record, editing a record, hiding a record, and sending a text.",
                ['log', 'audit', 'history', 'trail'],
                ['who-can-view-logs', 'what-does-dashboard-show', 'who-can-archive'],
            ),
            $this->question(
                'who-can-view-logs',
                'logs',
                'Who can see activity logs?',
                "Only a System Admin or Midwife. A Barangay Health Worker does not see this page.\n\nYou can search and pick a date. The staff home screen also shows the 3 latest actions.",
                ['who', 'permission', 'bhw logs'],
                ['what-are-activity-logs', 'what-can-bhw-do', 'what-does-dashboard-show'],
            ),
            $this->question(
                'what-does-dashboard-show',
                'dashboard',
                'What does the staff home screen show?',
                "It shows how many patients you have, how many checkups this month, how many vaccines, and how many mother visits.\n\nIt also shows simple charts for the last 6 months, the 3 latest actions, and the 3 latest news posts.",
                ['dashboard', 'stats', 'charts', 'overview'],
                ['what-do-immunization-statuses-mean', 'who-can-view-logs', 'how-do-announcements-work'],
            ),
            $this->question(
                'how-do-i-change-profile',
                'profile',
                'How do I change my password or photo?',
                "Tap your name or photo at the top right, then tap Settings.\n\nYou can add or remove a photo. To change your password, type your old password and a new one (at least 8 letters or numbers).\n\nYou cannot change your name, username, email, or job there. Ask a System Admin or Midwife to change those.",
                ['profile', 'password', 'photo', 'picture', 'settings'],
                ['who-can-create-users', 'how-do-i-log-out', 'what-are-activity-logs'],
            ),
            $this->question(
                'how-to-install-pwa',
                'pwa',
                'How do I put this on my phone?',
                "You can save this website on your phone, like an app, so you can open it from your home screen.\n\nOn this page, tap Add to phone.\n\nOn Android, follow the pop-up, or open the browser menu and choose Add to Home screen or Install app.\n\nOn iPhone, tap Share, then Add to Home Screen.",
                ['pwa', 'install', 'app', 'home screen', 'add to home'],
                ['does-pwa-work-offline', 'what-can-residents-see', 'what-is-the-system'],
            ),
            $this->question(
                'does-pwa-work-offline',
                'pwa',
                'Can I use it with no internet?',
                "If you already added it to your phone, it may still open a simple page when there is no signal.\n\nTo sign in and save records, you need internet.",
                ['offline', 'service worker', 'cache', 'no internet'],
                ['how-to-install-pwa', 'how-do-staff-log-in', 'what-can-residents-see'],
            ),
        ];
    }

    /**
     * @param  list<string>  $keywords
     * @param  list<string>  $related
     * @return array{id: string, category: string, question: string, answer: string, keywords: list<string>, related: list<string>}
     */
    private function question(
        string $id,
        string $category,
        string $question,
        string $answer,
        array $keywords,
        array $related,
    ): array {
        return [
            'id' => $id,
            'category' => $category,
            'question' => $question,
            'answer' => $answer,
            'keywords' => $keywords,
            'related' => $related,
        ];
    }
}
