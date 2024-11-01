import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import {__, _n, sprintf} from '@wordpress/i18n';
import './payment-fields/payment-fields.css';
import AbbrRequired from "./payment-fields/abbr-required";

class PaymentFields extends React.Component {
    static defaultProps = {
        showInstallment: false,
    };

    static propTypes = {
        showInstallment: PropTypes.bool,
    }

    constructor(props) {
        super(props);

        this.state = {
            cardNo: '',
            cardMasked: '',
            cardExpire: '',
            cardPw: '',
            idNo: '',
            installment: 1,
            userCards: [],
            userCard: null
        };

        this.$ = jQuery;

        this.formatMaskedNumber = this.formatMaskedNumber.bind(this);
        this.deleteCard = this.deleteCard.bind(this);
    }

    componentDidMount() {
        // this.$.ajax(opts.ajaxUrl.replace('###ENDPOINT###', 'shpg_get_user_cards'), {
        //     method: 'GET',
        //     data: {
        //         nonce: opts.nonce
        //     },
        // }).success(response => {
        //     if (response.success) {
        //         console.log('userCards', response.data);
        //         this.setState({
        //             userCards: response.data,
        //             userCard: response.data.length ? 0 : null,
        //         });
        //     }
        // });
    }

    formatMaskedNumber(value) {
        value = value.replace(/[^0-9*]/g, '').substring(0, 16);

        let parts = [
            value.substring(0, 4),
            value.substring(4, 8),
            value.substring(8, 12),
            value.substring(12, 16),
        ];

        if (parts[2].length) {
            parts[2] = parts[2].replace(/./g, '*');
        }

        return parts.join(' ').trim();
    }

    deleteCard(idx) {
        if (idx >= this.state.userCards.length) {
            return
        }

        const tokenId = this.state.userCards[idx].id;

        if (tokenId) {
            this.$.ajax(opts.ajaxUrl.replace('###ENDPOINT###', 'shpg_remove_user_card'), {
                method: 'POST',
                data: {
                    nonce: opts.nonce,
                    token_id: tokenId
                }
            }).success(response => {
                if (response.success) {
                    this.setState(({userCards, userCard}) => {
                        userCards.splice(idx, 1);
                        if (userCards.length >= userCard) {
                            userCard = userCards.length - 1;
                        }
                        return {
                            userCards: userCards,
                            userCard: userCard,
                        }
                    });
                }
            })
        }
    }

    render() {
        const showInstallment = this.props.showInstallment,
            userCards = this.state.userCards;

        return (
            <>
                <div id="shpg-manage-cards" className="shpg-fields-wrap" style={{display: 'none'}}>
                    {
                        userCards.length > 0 ?
                            <div>
                                <hr/>
                                <p>
                                    {sprintf(
                                        _n('You already have %d card .', 'You already have %d cards.', userCards.length, 'shoplic-pg'),
                                        userCards.length
                                    )}
                                    <br/>
                                    {__('Choose a card or register a new card.', 'shoplic-pg')}
                                </p>
                                <ul className="shpg-cards-list">
                                    <li>
                                        <input
                                            id="shpg_user_card-none"
                                            type="radio"
                                            name="shpg_user_card"
                                            defaultValue=""
                                            defaultChecked={false}
                                            onClick={() => {
                                                this.setState({userCard: null})
                                            }}
                                        />
                                        <label htmlFor="shpg_user_card-none">
                                            {__('Do not choose', 'shoplic-pg')}
                                        </label>
                                    </li>
                                    {userCards.map((userCard, idx) => {
                                        return (
                                            <li key={userCard.id}>
                                                <input
                                                    id={"shpg_user_card-" + userCard.id}
                                                    type="radio"
                                                    name="shpg_user_card"
                                                    defaultValue={userCard.id}
                                                    defaultChecked={this.state.userCards === idx}
                                                    onClick={() => {
                                                        this.setState({userCard: idx})
                                                    }}
                                                />
                                                <label htmlFor={"shpg_user_card-" + userCard.id}>
                                                    {
                                                        userCard.card_name +
                                                        ' ' +
                                                        (userCard.is_check ? __('(check)', 'shoplic-pg') : '') +
                                                        ' ' +
                                                        userCard.card_no
                                                    }
                                                </label>
                                                <span className="shpg-remove-card">
                                                    [
                                                    <a
                                                        href="#"
                                                        onClick={e => {
                                                            e.preventDefault();
                                                            if (confirm(__('Are you sure you want to delete this card information?', 'shoplic-pg'))) {
                                                                this.deleteCard(idx)
                                                            }
                                                        }}
                                                    >
                                                        {__('Delete', 'shoplic-pg')}
                                                    </a>
                                                    ]
                                                </span>
                                            </li>
                                        )
                                    })}
                                </ul>
                            </div>
                            :
                            // No cards
                            <p className="form-row form-row-wide">
                                No cards found.
                            </p>
                    }
                </div>
                <div id="shpg-register-card" className="shpg-fields-wrap">
                    <fieldset>
                        <p className="form-row form-row-first validate-required">
                            <label htmlFor="shpg-card-no">{__('Card No.', 'shoplic-pg')} <AbbrRequired/></label>
                            <span className="woocommerce-input-wrapper">
                                <input
                                    id="shpg-card-masked"
                                    className="input-text"
                                    type="text"
                                    pattern="\d{4} \d{4} \d{4} \d{4}"
                                    value={this.state.cardMasked}
                                    placeholder="**** **** **** ****"
                                    onChange={(e) => {
                                        const value = e.target.value.replace(/[^0-9*]/g, '').substring(0, 16);
                                        this.setState(({cardNo}) => {
                                            if (value.length > cardNo.length) {
                                                return {
                                                    cardNo: cardNo + value.substring(cardNo.length),
                                                    cardMasked: this.formatMaskedNumber(value),
                                                };
                                            } else {
                                                return {
                                                    cardNo: cardNo.substring(0, value.length),
                                                    cardMasked: this.formatMaskedNumber(value),
                                                };
                                            }
                                        });
                                    }}
                                />
                                <input
                                    id="shpg-card-no"
                                    name="shpg[card_no]"
                                    className="input-text"
                                    type="hidden"
                                    defaultValue={this.state.cardNo}
                                />
                            </span>
                        </p>
                        <p className="form-row form-row-last validate-required">
                            <label htmlFor="shpg-card-expire">{__('Card Expire', 'shoplic-pg')} <AbbrRequired/></label>
                            <span className="woocommerce-input-wrapper">
                                <input
                                    id="shpg-card-expire"
                                    name="shpg[card_expire]"
                                    className="input-text"
                                    type="text"
                                    pattern="\d{2} / \d{2}"
                                    value={this.state.cardExpire}
                                    placeholder="MM / YY"
                                    onChange={(e) => {
                                        let value = e.target.value.replace(/[^0-9]/g, '').substring(0, 4);

                                        if (1 === value.length && parseInt(value) > 1) {
                                            value = '0' + value
                                        } else if (2 === value.length) {
                                            let month = parseInt(value);
                                            if (month < 1 || month > 12) {
                                                value = '';
                                            }
                                        } else if (value.length > 2) {
                                            value = value.substring(0, 2) + ' / ' + value.substring(2)
                                        }

                                        this.setState({cardExpire: value})
                                    }}
                                />
                            </span>
                        </p>
                        <p className="form-row form-row-first validate-required">
                            <label htmlFor="shpg-card-pw">
                                {__('Card Pw. (2 digits)', 'shoplic-pg')}
                                <AbbrRequired/>
                            </label>
                            <span className="woocommerce-input-wrapper">
                                <input
                                    id="shpg-card-pw"
                                    name="shpg[card_pw]"
                                    type="password"
                                    className="input-text"
                                    value={this.state.cardPw}
                                    onChange={(e) => {
                                        this.setState({
                                            cardPw: e.target.value.replace(/[^0-9]/g, '').substring(0, 2)
                                        })
                                    }}
                                />
                            </span>
                        </p>
                        {showInstallment &&
                            <p className="form-row form-row-last validate-required">
                                <label htmlFor="shpg-installment"> {__('Installment', 'shoplic-pg')} </label>
                                <span className="woocommerce-input-wrapper">
                                <input
                                    id="shpg-installment"
                                    name="shpg[installment]"
                                    className="input-text"
                                    type="number"
                                    min="1"
                                    max="24"
                                    value={this.state.installment}
                                    onChange={(e) => {
                                        this.setState({installment: parseInt(e.target.value)})
                                    }}
                                />
                            </span>
                            </p>
                        }
                        <p className="form-row form-row-wide validate-required">
                            <label htmlFor="shpg-id-no">
                                {__('Birthday 6 chars. / business number 10 chars.', 'shoplic-pg')}
                                <AbbrRequired/>
                            </label>
                            <span className="woocommerce-input-wrapper">
                                <input
                                    id="shpg-id-no"
                                    name="shpg[id_no]"
                                    type="text"
                                    className="input-text"
                                    value={this.state.idNo}
                                    onChange={(e) => {
                                        this.setState({
                                            idNo: e.target.value.replace(/[^0-9]/g, '').substring(0, 10)
                                        })
                                    }}
                                />
                            </span>
                        </p>
                    </fieldset>
                </div>
            </>
        );
    }
}

/* globals jQuery, shpgNicepayBilling */
const opts = shpgNicepayBilling || {
    ajaxUrl: '',
    nonce: '',
    isCheckoutPayPage: 'no'
};

if ('yes' === opts.isCheckoutPayPage) {
    ReactDOM.render(
        <PaymentFields/>,
        document.getElementById('shpg-nicepay-billing')
    );
} else {
    jQuery(document.body).on('updated_checkout', function () {
        ReactDOM.render(
            <PaymentFields/>,
            document.getElementById('shpg-nicepay-billing')
        );
    });
}

