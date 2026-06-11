import type { AxiosError } from 'axios';

import { ApiError  } from '@/core/api/types/api-error';
import type {ApiErrorBody} from '@/core/api/types/api-error';
import { session } from '@/core/auth/session';

export function normalizeApiError(error: AxiosError<ApiErrorBody>): never {
    const status = error.response?.status ?? 500;
    const body = error.response?.data;

    if (status === 401) {
        session.clear();
    }

    throw new ApiError(body?.message ?? error.message, status, body?.errors);
}
