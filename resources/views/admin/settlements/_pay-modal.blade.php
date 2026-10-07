{{-- UPI pay modal, driven by public/js/upi-pay.js. Two-column (QR | details) on tablet/desktop. --}}
<div class="modal fade glass-modal pay-modal" id="payModal" tabindex="-1" aria-labelledby="payModalTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" id="payForm" action="#" novalidate>
            @csrf
            <input type="hidden" name="note" id="payNote" value="">
            <button type="button" class="close pay-close" data-dismiss="modal" aria-label="Close">&times;</button>

            {{-- Step 1: scan & pay --}}
            <div class="pay-step" id="payStep">
                <div class="pay-qr-col" id="qrSection">
                    <div class="qr-frame" id="qrFrame">
                        <svg class="qr-ring" viewBox="0 0 100 100" aria-hidden="true"><rect x="1.5" y="1.5" width="97" height="97" rx="14" pathLength="100" id="qrRing"/></svg>
                        <div id="qrCode" aria-label="UPI payment QR code" role="img"></div>
                        <div class="qr-expired" id="qrExpired" hidden>
                            <i class="fas fa-hourglass-end"></i>
                            <div class="font-weight-bold">QR expired</div>
                            <button type="button" class="btn btn-primary btn-sm mt-2" id="qrRegenerate"><i class="fas fa-redo mr-1"></i>New QR</button>
                        </div>
                    </div>
                    <div class="qr-meta"><i class="far fa-clock"></i> Expires in <b id="qrTimer">5:00</b></div>
                    <div class="qr-apps">Scan with GPay, PhonePe, Paytm or any UPI app</div>
                    <div class="upi-apps d-md-none" id="upiApps">
                        <div class="upi-apps-title">Pay from this phone</div>
                        <div class="upi-apps-grid" id="upiAppsGrid"></div>
                        <div class="upi-apps-note" id="upiAppsNote"></div>
                    </div>
                </div>
                <div class="pay-qr-col pay-no-upi" id="noUpiWarning" hidden>
                    <i class="fas fa-qrcode"></i>
                    <div class="font-weight-bold mt-2">No UPI ID</div>
                    <div class="small">This employee hasn't added a UPI ID, so a QR can't be generated. Pay another way, then record it.</div>
                </div>

                <div class="pay-info-col">
                    <div class="pay-head">
                        <div class="pay-avatar" id="payAvatar">A</div>
                        <div class="pay-who">
                            <div class="pay-eyebrow">Paying</div>
                            <h5 class="pay-name" id="payModalTitle"><span id="payName">-</span></h5>
                            <button type="button" class="pay-upi" id="payUpiCopy" title="Copy UPI ID">
                                <i class="fas fa-at"></i><span id="payUpi">-</span><i class="far fa-copy pay-copy-icon"></i>
                            </button>
                        </div>
                    </div>

                    <label for="payAmount" class="pay-label">Amount</label>
                    <div class="pay-amount">
                        <span class="pay-currency">₹</span>
                        <input type="number" inputmode="decimal" step="0.01" min="0.01" name="amount" id="payAmount" required>
                    </div>
                    <div class="pay-chips">
                        <button type="button" class="pay-chip" data-fill="1">Full · <span id="payPending">-</span></button>
                        <button type="button" class="pay-chip" data-fill="0.5">Half</button>
                    </div>
                    <div class="pay-error" id="payError" hidden></div>

                    <div class="pay-foot">
                        <button type="submit" class="btn btn-success btn-lg btn-block pay-submit" id="payConfirm">
                            <span class="pay-submit-text"><i class="fas fa-check-circle mr-1"></i> I've completed the payment</span>
                            <span class="pay-submit-busy"><span class="spinner-border spinner-border-sm mr-2"></span>Confirming…</span>
                        </button>
                        <div class="pay-hint">Tap after the UPI app shows success.</div>
                    </div>
                </div>
            </div>

            {{-- Step 2: success (shown only after the server confirms) --}}
            <div class="pay-success" id="paySuccess" hidden aria-live="polite">
                <div class="confetti" id="confetti" aria-hidden="true"></div>
                <svg class="success-check" viewBox="0 0 120 120" aria-hidden="true">
                    <circle class="success-circle" cx="60" cy="60" r="52"/>
                    <path class="success-tick" d="M38 62 l15 15 l30 -32"/>
                </svg>
                <div class="success-amount" id="successAmount">₹0.00</div>
                <div class="success-title">Payment recorded</div>
                <div class="success-sub">Paid to <b id="successName">-</b></div>
                <div class="success-receipt">
                    <div><span>Date</span><b id="successDate">-</b></div>
                    <div><span>Payment ID</span><b id="successRef">-</b></div>
                    <div><span>Still pending</span><b id="successRemaining">-</b></div>
                </div>
                <button type="button" class="btn btn-primary btn-lg btn-block" id="successDone">Done</button>
            </div>
        </form>
    </div>
</div>
