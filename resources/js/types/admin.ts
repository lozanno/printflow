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

export type PricingStrategy =
    'PER_UNIT_TIERED' | 'PER_AREA' | 'PER_AREA_WITH_SETUP';

export type AttachedComponent = Component & {
    pivot: {
        sort_order: number;
        is_required: boolean;
    };
};

export type ProductTemplate = {
    id: number;
    code: string;
    name: string;
    pricing_strategy: PricingStrategy;
    components_count?: number;
    components?: AttachedComponent[];
};
