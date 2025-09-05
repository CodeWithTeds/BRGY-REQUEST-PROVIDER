import { useForm } from '@inertiajs/vue3'
import { makeMapLabel } from '@/composables/useLabels'

export function useBarangayClearanceForm() {
    const form = useForm({
        first_name: '',
        middle_name: '',
        last_name: '',
        date_of_birth: '',
        place_of_birth: '',
        civil_status: '',
        gender: '',
        citizenship: '',
        contact_number: '',
        email: '',
        purpose: '',

        address_type: '',
        house_no: '',
        street: '',
        purok: '',
        region_code: '',
        province_code: '',
        city_code: '',
        barangay_code: '',
        zip_code: '',

        document_type: '',
        document: null as File | null,
    })

    const civilStatusOptions = {
        single: 'Single',
        married: 'Married',
        widowed: 'Widowed',
        separated: 'Separated',
    }

    const genderOptions = {
        male: 'Male',
        female: 'Female',
        other: 'Other',
    }

    const documentTypeOptions = {
        certificate_of_residency: 'Certificate of Residency',
        lease_contract: 'Lease Contract',
        utility_bill: 'Utility Bill',
    }

    const addressTypeOptions = {
        present: 'Present Address',
        permanent: 'Permanent Address',
    }

    const civilStatusLabel = makeMapLabel(() => form.civil_status, civilStatusOptions)
    const genderLabel = makeMapLabel(() => form.gender, genderOptions)
    const documentTypeLabel = makeMapLabel(() => form.document_type, documentTypeOptions)
    const addressTypeLabel = makeMapLabel(() => form.address_type, addressTypeOptions)

    return {
        form,
        civilStatusLabel,
        genderLabel,
        documentTypeLabel,
        addressTypeLabel,
    }
}