export type InputType = 'CHOICE' | 'NUMBER' | 'DIMENSIONS';

export type ComponentOption = {
    id: number;
    component_id: number;
    value: string;
    label: string;
    sort_order: number;
};

export type Component = {
    id: number;
    code: string;
    label: string;
    input_type: InputType;
    options_count?: number;
    options?: ComponentOption[];
};
