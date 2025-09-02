import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useBarangayClearanceForm() {
    const form = useForm({
        // Personal Information
        first_name: '',
        middle_name: '',
        last_name: '',
        birth_date: '',
        civil_status: '',
        gender: '',
        contact_number: '',
        email: '',
        purpose: '',

        // Address Information
        address_type: 'present',
        region_code: '',
        province_code: '',
        city_code: '',
        barangay_code: '',
        house_no: '',
        street: '',
        purok: '',

        // Supporting Documents
        document_type: '',
        document_file: null as File | null,
    });

    const civilStatusLabel = computed(() => ({
        single: 'Single',
        married: 'Married',
        widowed: 'Widowed',
        divorced: 'Divorced',
        separated: 'Separated'
    }));

    const genderLabel = computed(() => ({
        male: 'Male',
        female: 'Female',
        other: 'Other'
    }));

    const documentTypeLabel = computed(() => ({
        valid_id: 'Valid ID',
        proof_of_residence: 'Proof of Residence',
        other: 'Other Supporting Document'
    }));

    const addressTypeLabel = computed(() => ({
        present: 'Present Address',
        permanent: 'Permanent Address'
    }));

    return {
        form,
        civilStatusLabel,
        genderLabel,
        documentTypeLabel,
        addressTypeLabel,
    };
}