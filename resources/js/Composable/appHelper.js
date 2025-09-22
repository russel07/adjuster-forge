import { useRestApi } from '../http/rest_request';

export function useAppHelper() {
    const { get, post, del, put, patch } = useRestApi();

    function ucFirst(text) {
        return text[0].toUpperCase() + text.slice(1).toLowerCase();
    }

    function ucWords(text) {
        return text
            .split(/\s+/)
            .map((word) => word[0].toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }

    function imageUrl(base_url, fileName) {
        if (!base_url || !fileName) {
            return '';
        }
        return `${base_url}/${fileName}`;
    }

    function getStatusLabel(value, user_type = '') {
        switch (value) {
            case 'pending': return 'Pending';
            case 'completed': return 'Completed';
            case 'submitted': return user_type === 'driver' ? 'Under Review' : 'Submitted for Review';
            case 'rejected': return 'Rejected';
            case 'revoked': return 'Revoked';
            case 'active': return 'Active';
            case 'expire': return 'Expire';
            case 'due_subscription': return 'Due Subscription';
            case 'approved': return 'Approved';
            case 'deactivated': return 'Deactivated';
            case 'profile_completed': return user_type === 'driver' ? 'Under Review' : 'Due Subscription';
            case 'not_completed': return 'Not Completed';
            default: return value || 'Unknown';
        }
    };

    function getStatus(value, user_type = '') {
        switch (value) {
            case 'pending': return 'pending';
            case 'completed': return 'completed';
            case 'submitted': return user_type === 'driver' ? 'under_review' : 'submitted_for_review';
            case 'rejected': return 'rejected';
            case 'revoked': return 'revoked';
            case 'active': return 'active';
            case 'expire': return 'expire';
            case 'due_subscription': return 'due_subscription';
            case 'approved': return 'approved';
            case 'deactivated': return 'deactivated';
            case 'profile_completed': return user_type === 'driver' ? 'under_review' : 'due_subscription';
            case 'not_completed': return 'not_completed';
            default: return value || 'unknown';
        }
    }

    return {
        get,
        post,
        del,
        put,
        patch,
        ucFirst,
        ucWords,
        imageUrl,
        getStatusLabel,
        getStatus
    };
}