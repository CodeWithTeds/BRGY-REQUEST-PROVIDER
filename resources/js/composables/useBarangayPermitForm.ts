
import { useForm } from '@inertiajs/vue3'
import { makeMapLabel } from '@/composables/useLabels'

export function useBarangayPermitForm() {
    const form = useForm({
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

        // PSGC codes
        region_code: null as string | null,
        province_code: null as string | null,
        city_code: null as string | null,
        barangay_code: null as string | null,
        zip_code: '',
        document_type: 'certificate_of_residency',
        document: null as File | null,
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
