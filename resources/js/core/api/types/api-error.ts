export interface ValidationErrors {
    [field: string]: string[];
}

export interface ApiErrorBody {
    message: string;
    errors?: ValidationErrors;
}

export class ApiError extends Error {
    constructor(
        message: string,
        public readonly status: number,
        public readonly errors?: ValidationErrors,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}
