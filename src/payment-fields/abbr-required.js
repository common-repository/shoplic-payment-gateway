import React from 'react';
import {__} from "@wordpress/i18n";

function AbbrRequired() {
    return <abbr className="required" title={__('required', 'snpb')}>*</abbr>;
}

export default AbbrRequired;
