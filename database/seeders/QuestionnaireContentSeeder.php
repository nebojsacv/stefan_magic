<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireTemplate;
use Illuminate\Database\Seeder;

class QuestionnaireContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTier('low', 'Low-Risk Vendor Assessment', $this->lowQuestions());
        $this->seedTier('medium', 'Medium-Risk Vendor Assessment', $this->mediumQuestions());
        $this->seedTier('high', 'High-Risk Vendor Assessment', $this->highQuestions());
    }

    private function seedTier(string $riskLevel, string $name, array $questions): void
    {
        $template = QuestionnaireTemplate::firstOrCreate(
            ['risk_level' => $riskLevel],
            [
                'name' => $name,
                'question_count' => count($questions),
                'is_nis2_compliant' => true,
                'is_active' => true,
                'description' => "NIS2-compliant {$riskLevel}-tier assessment with ".count($questions).' questions.',
            ]
        );

        $template->update([
            'name' => $name,
            'question_count' => count($questions),
            'is_nis2_compliant' => true,
            'is_active' => true,
        ]);

        // Clear and re-seed questions for this template
        $questionIds = $template->questions()->pluck('id');
        QuestionnaireAnswer::whereIn('question_id', $questionIds)->delete();
        Option::whereIn('question_id', $questionIds)->delete();
        $template->questions()->delete();

        foreach ($questions as $index => $q) {
            $question = Question::create([
                'template_id' => $template->id,
                'question_text' => $q['question'],
                'question_category' => $q['section'],
                'type' => 'radio',
                'need_evidence' => isset($q['evidence']),
                'order_index' => $index + 1,
                'scoring_weight' => $q['weight'],
            ]);

            $this->createOptions($question, $q['answers'], isset($q['evidence']));
        }
    }

    private function createOptions(Question $question, string $answers, bool $hasEvidence): void
    {
        $optionSets = [
            'Yes/No' => [
                ['text' => 'Yes', 'risk_value' => 1],
                ['text' => 'No', 'risk_value' => 3],
            ],
            'Yes/No/N/A' => [
                ['text' => 'Yes', 'risk_value' => 1],
                ['text' => 'No', 'risk_value' => 3],
                ['text' => 'N/A', 'risk_value' => 0],
            ],
        ];

        $options = $optionSets[$answers] ?? $optionSets['Yes/No'];

        foreach ($options as $i => $option) {
            Option::create([
                'question_id' => $question->id,
                'option_text' => $option['text'],
                'risk_value' => $option['risk_value'],
                'order_index' => $i + 1,
            ]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function lowQuestions(): array
    {
        return [
            ['section' => 'Governance', 'question' => 'Do you have a person responsible for managing IT/cybersecurity in your company?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Name/role description or organizational chart'],
            ['section' => 'Access & Devices', 'question' => 'Do your employees use passwords, PINs, or screen locks on their devices?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Policy excerpt or screenshot of device policy settings'],
            ['section' => 'Awareness', 'question' => 'Do your employees receive basic cybersecurity training?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Training records, agenda, or example certificate'],
            ['section' => 'Access Control', 'question' => 'Do you restrict access to systems based on job roles?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Access control policy or simple RBAC overview'],
            ['section' => 'Access Control', 'question' => 'Do you remove employee access immediately when staff leave the company?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Offboarding procedure or checklist'],
            ['section' => 'Endpoint Security', 'question' => 'Do you use antivirus/antimalware protection on company devices?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Screenshot or report from AV/EDR tool'],
            ['section' => 'Patch Management', 'question' => 'Do you install security updates regularly on employee devices?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Update policy or OS update configuration screenshot'],
            ['section' => 'Data Protection', 'question' => 'Do you process any personal data on behalf of our organization?', 'answers' => 'Yes/No/N/A', 'weight' => 2, 'evidence' => 'Data processing description or DPA'],
            ['section' => 'Contractual', 'question' => 'Does our contract include confidentiality obligations?', 'answers' => 'Yes/No', 'weight' => 2, 'evidence' => 'Contract excerpt with confidentiality clause'],
            ['section' => 'Data Location', 'question' => 'Do you store or process any of our information outside the EU/EEA?', 'answers' => 'Yes/No/N/A', 'weight' => 2, 'evidence' => 'List of data locations or provider statement'],
            ['section' => 'Incident Management', 'question' => 'Do you have a process to notify us if a cyber incident affects your service?', 'answers' => 'Yes/No', 'weight' => 2, 'evidence' => 'Incident notification procedure or communication template'],
            ['section' => 'Data Deletion', 'question' => 'Will you delete our information when the contract ends or when requested?', 'answers' => 'Yes/No', 'weight' => 2, 'evidence' => 'Data deletion or retention policy'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function mediumQuestions(): array
    {
        return [
            ['section' => 'Governance', 'question' => 'Do you maintain a documented cybersecurity or information security policy?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Information security/cybersecurity policy document'],
            ['section' => 'Governance', 'question' => 'Do you perform cybersecurity risk assessments at least annually?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Latest risk assessment summary or report'],
            ['section' => 'Access Control', 'question' => 'Do you enforce Multi-Factor Authentication (MFA) for administrative or privileged accounts?', 'answers' => 'Yes/No/N/A', 'weight' => 4, 'evidence' => 'MFA configuration screenshot or policy'],
            ['section' => 'Access Control', 'question' => 'Do you implement role-based access control (RBAC) for your systems?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'RBAC matrix or access control policy'],
            ['section' => 'Access Control', 'question' => 'Do you monitor privileged account activity?', 'answers' => 'Yes/No/N/A', 'weight' => 3, 'evidence' => 'Logs or monitoring configuration for privileged accounts'],
            ['section' => 'Vulnerability Management', 'question' => 'Do you regularly scan your systems for vulnerabilities?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Recent vulnerability scan report (redacted)'],
            ['section' => 'Patch Management', 'question' => 'Do you apply security patches within defined timelines?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Patch management policy or process description'],
            ['section' => 'Asset Management', 'question' => 'Do you maintain an inventory of your IT assets relevant to the service?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Asset inventory export or sample'],
            ['section' => 'Logging & Monitoring', 'question' => 'Do you log security-relevant events on systems supporting this service?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Logging configuration or SIEM screenshot'],
            ['section' => 'Logging & Monitoring', 'question' => 'Do you retain security logs for at least 3 months?', 'answers' => 'Yes/No/N/A', 'weight' => 3, 'evidence' => 'Log retention settings or policy'],
            ['section' => 'Data Protection', 'question' => 'Do you encrypt data at rest for this service?', 'answers' => 'Yes/No/N/A', 'weight' => 4, 'evidence' => 'Storage/database encryption settings'],
            ['section' => 'Data Protection', 'question' => 'Do you encrypt data in transit using TLS 1.2 or higher?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'TLS configuration or test result'],
            ['section' => 'Data Protection', 'question' => 'Do you have a documented data retention and deletion policy?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Data retention/deletion policy excerpt'],
            ['section' => 'Incident Management', 'question' => 'Do you maintain a documented Incident Response Plan (IRP)?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Incident Response Plan document'],
            ['section' => 'Incident Management', 'question' => 'Do you notify customers if a security incident affects their data or service?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Incident communication procedure or contract clause'],
            ['section' => 'Incident Management', 'question' => 'Do you conduct incident response exercises (e.g. tabletop tests)?', 'answers' => 'Yes/No/N/A', 'weight' => 2, 'evidence' => 'Exercise or test report'],
            ['section' => 'Subcontractors', 'question' => 'Do you use subcontractors to deliver any part of this service?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'List of subcontractors (if any)'],
            ['section' => 'Subcontractors', 'question' => 'If you use subcontractors, do you review their cybersecurity posture?', 'answers' => 'Yes/No/N/A', 'weight' => 3, 'evidence' => 'Subcontractor assessment template or report'],
            ['section' => 'Business Continuity', 'question' => 'Do you have a backup or continuity strategy for this service?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Backup/continuity plan summary'],
            ['section' => 'Business Continuity', 'question' => 'Do you test backup restore or continuity at least once per year?', 'answers' => 'Yes/No/N/A', 'weight' => 2, 'evidence' => 'Backup restore test report or evidence'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function highQuestions(): array
    {
        return [
            ['section' => 'Governance & Risk', 'question' => 'Do you maintain a formal cybersecurity framework (e.g. ISO 27001, NIST CSF)?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'Certificate, SoA, or documented framework mapping'],
            ['section' => 'Governance & Risk', 'question' => 'Do you perform regular risk assessments covering cyber and operational risk?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'Risk assessment report or summary'],
            ['section' => 'Governance & Risk', 'question' => 'Have you assigned responsibility for cybersecurity to a dedicated senior leader?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Org chart or role description'],
            ['section' => 'Governance & Risk', 'question' => 'Do you perform external penetration testing at least annually?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'Executive summary of recent penetration test'],
            ['section' => 'Access & Identity', 'question' => 'Do you enforce Multi-Factor Authentication (MFA) for all users of this service?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'MFA configuration screenshots or policy'],
            ['section' => 'Access & Identity', 'question' => 'Do you enforce least-privilege access using role-based access control (RBAC)?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'RBAC matrix or policy'],
            ['section' => 'Access & Identity', 'question' => 'Do you monitor and alert on privileged account activity?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'SIEM/monitoring configuration or sample alerts'],
            ['section' => 'Technical Controls', 'question' => 'Do you deploy EDR/antimalware on all critical servers and endpoints?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'EDR dashboard screenshot or deployment summary'],
            ['section' => 'Technical Controls', 'question' => 'Do you perform continuous or frequent vulnerability scanning on relevant assets?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'Recent vulnerability scan reports'],
            ['section' => 'Technical Controls', 'question' => 'Do you maintain secure configuration baselines (e.g. CIS benchmarks) for systems?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Secure configuration baseline documents'],
            ['section' => 'Technical Controls', 'question' => 'Do you apply security patches according to strict SLAs (e.g. critical within 7 days)?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Patch management policy and SLA'],
            ['section' => 'Data Protection', 'question' => 'Is data at rest for this service encrypted?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'Disk/storage or database encryption configuration'],
            ['section' => 'Data Protection', 'question' => 'Is data in transit for this service encrypted using TLS 1.2 or higher?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'TLS configuration or external test results'],
            ['section' => 'Logging & Monitoring', 'question' => 'Do you maintain centralized logging (e.g. SIEM) for security events?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'SIEM overview or configuration'],
            ['section' => 'Logging & Monitoring', 'question' => 'Do you retain security logs for at least 6–12 months?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Log retention settings or policy'],
            ['section' => 'Secure Software Supply Chain', 'question' => 'Do you maintain a Software Bill of Materials (SBOM) for your software components?', 'answers' => 'Yes/No/N/A', 'weight' => 4, 'evidence' => 'SBOM export or tool screenshot'],
            ['section' => 'Secure Software Supply Chain', 'question' => 'Do you scan third-party libraries and dependencies for vulnerabilities (SCA)?', 'answers' => 'Yes/No/N/A', 'weight' => 4, 'evidence' => 'SCA scan report or tool output'],
            ['section' => 'Secure Software Supply Chain', 'question' => 'Do you use code signing or integrity controls for critical software components?', 'answers' => 'Yes/No/N/A', 'weight' => 3, 'evidence' => 'Code signing configuration or documentation'],
            ['section' => 'Secure Software Supply Chain', 'question' => 'Do you follow a documented Secure Software Development Lifecycle (SSDLC)?', 'answers' => 'Yes/No/N/A', 'weight' => 4, 'evidence' => 'SSDLC policy or pipeline diagram'],
            ['section' => 'Secure Software Supply Chain', 'question' => 'Do you use automated tools (SAST/DAST) to detect insecure code?', 'answers' => 'Yes/No/N/A', 'weight' => 3, 'evidence' => 'SAST/DAST reports or pipeline configuration'],
            ['section' => 'Network & Cloud Security', 'question' => 'Do you segment networks to limit the blast radius of potential attacks?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Network architecture diagram showing segmentation'],
            ['section' => 'Network & Cloud Security', 'question' => 'Do you restrict production access using secure methods (VPN, jump hosts, PAM)?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Access control design or PAM configuration'],
            ['section' => 'Network & Cloud Security', 'question' => 'Do you use firewalls or security groups to restrict inbound and outbound traffic?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Firewall or security group rules (sanitized)'],
            ['section' => 'Incident Response', 'question' => 'Do you maintain and regularly update an Incident Response Plan for this service?', 'answers' => 'Yes/No', 'weight' => 5, 'evidence' => 'Incident Response Plan and revision history'],
            ['section' => 'Incident Response', 'question' => 'Can you notify customers within 24 hours when a significant security incident is detected?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Incident communication policy or SLA clause'],
            ['section' => 'Incident Response', 'question' => 'Do you conduct cyber crisis or incident response simulations at least annually?', 'answers' => 'Yes/No', 'weight' => 3, 'evidence' => 'Crisis exercise or tabletop test report'],
            ['section' => 'Business Continuity & DR', 'question' => 'Do you maintain a documented Disaster Recovery Plan for this service?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Disaster Recovery Plan document'],
            ['section' => 'Business Continuity & DR', 'question' => 'Do you perform BCP/DR tests at least annually?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'BCP/DR test reports'],
            ['section' => 'Supplier/Subcontractor Risk', 'question' => 'Do you assess third-party subcontractors before onboarding them?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Subcontractor risk assessment template or report'],
            ['section' => 'Supplier/Subcontractor Risk', 'question' => 'Do you require subcontractors to implement equivalent cybersecurity controls?', 'answers' => 'Yes/No', 'weight' => 4, 'evidence' => 'Contract clauses or security requirements for subcontractors'],
        ];
    }
}
