
import { useForm } from '@inertiajs/vue3'
import { makeMapLabel } from '@/composables/useLabels'

export function useBarangayPermitForm() {
    const form = useForm<{
        first_name: string;
        middle_name: string;
        last_name: string;
        suffix: string;
        date_of_birth: string;
        place_of_birth: string;
        civil_status: string;
        gender: string;
        citizenship: string;
        contact_number: string;
        address_type: string;
        house_no: string;
        street: string;
        purok: string;
        region_code: string | null;
        province_code: string | null;
        city_code: string | null;
        barangay_code: string | null;
        zip_code: string;
        document_type: string;
        document: File | null;
        errors: Record<string, string>;
    }>({
        first_name: '',
        middle_name: '',
        last_name: '',
        suffix: '',
        date_of_birth: '',
        place_of_birth: '',
        civil_status: 'single',
        gender: 'male',
        citizenship: '',
        contact_number: '',
        address_type: 'present',
        house_no: '',
        street: '',
        purok: '',
        region_code: null,
        province_code: null,
        city_code: null,
        barangay_code: null,
        zip_code: '',
        document_type: 'certificate_of_residency',
        document: null,
        errors: {}
    })

    // Centralized labels
    const civilStatusLabel = makeMapLabel(() => form.civil_status, {
        single: 'Single',
        married: 'Married',
        widowed: 'Widowed',
        separated: 'Separated',
    })

    const genderLabel = makeMapLabel(() => form.gender, {
        male: 'Male',
        female: 'Female',
        other: 'Other',
    })

    const addressTypeLabel = makeMapLabel(() => form.address_type, {
        present: 'Present',
        permanent: 'Permanent',
    })

    const documentTypeLabel = makeMapLabel(() => form.document_type, {
        certificate_of_residency: 'Certificate of Residency',
        lease_contract: 'Lease Contract',
        utility_bill: 'Utility Bill',
    })

    return {
        form,
        civilStatusLabel,
        genderLabel,
        addressTypeLabel,
        documentTypeLabel,
    }
}
