# UML — ksf_payment_destinations

## Class Diagram (text)

```
┌─────────────────────────────────────────────────┐
│              hooks (FA base class)               │
│  + install_options($app)                         │
│  + install_access()                              │
│  + db_prewrite(&$cart, $trans_type)              │
└──────────────────────┬──────────────────────────┘
                       │ extends
┌──────────────────────▼──────────────────────────┐
│     hooks_ksf_payment_destinations               │
│  - $module_name = 'ksf_payment_destinations'     │
│  + install_options($app)                         │
│  + install_access()                              │
│  + db_prewrite(&$cart, $trans_type)              │
│        uses ──────────┐                          │
└───────────────────────┤                         │
                        │                         │
┌───────────────────────▼─────────────────────────┐
│   ksf_payment_destinations_model                 │
│   (extends generic_fa_interface_model)           │
│  - $payment_term : int                           │
│  - $payment_term_name : string                   │
│  - $bank_account : int                           │
│  - $bank_account_name : string                   │
│  + __construct($prefs, $controller)              │
│  + getPaymentTerms() : array                     │
│  + getBankAccountFromTerm() : int                │
│  + define_table()                                │
│  + insert_data($arr)                             │
│        uses ──────┐                              │
│                   │                              │
│   ┌───────────────▼──────────────┐               │
│   │      table_interface         │               │
│   │  + table_details['tablename']│               │
│   │  + fields_array[]            │               │
│   │  + table_details['primarykey']│              │
│   └──────────────────────────────┘               │
└──────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────┐
│     ksf_payment_destinations                     │
│   (extends generic_fa_interface_controller)      │
│  + __construct($prefs)                           │
│  + handle_edit()                                 │
│  + handle_delete()                               │
│  + run()                                         │
│  + action_show_form()                            │
│  + install()                                     │
│  + master_form()                                 │
│  has-a ──▶ model : ksf_payment_destinations_model│
│  has-a ──▶ view  : ksf_payment_destinations_view │
└──────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────┐
│     ksf_payment_destinations_view                │
│   (extends generic_fa_interface_view)            │
│  + __construct($prefs, $controller)              │
│  + usage_form()                                  │
│  + comboBankAccountList()                        │
│  + comboPaymentList()                            │
│  + form_header()                                 │
│  + form_item_rows()                              │
│  + form_add($action, $msg)                       │
│  + edit_item_form()                              │
│  + item_form()                                   │
│  + master_form()                                 │
│  + line_start_focus()                            │
│  + form_ksf_payment_destinations_completed()     │
└──────────────────────────────────────────────────┘
```

## Sequence: Invoice Payment Redirect

```
User/POS          FA Core            hooks_pd           Model            DB
  │                 │                  │                  │                │
  │  submit invoice │                  │                  │                │
  │────────────────▶│                  │                  │                │
  │                 │  db_prewrite()   │                  │                │
  │                 │─────────────────▶│                  │                │
  │                 │                  │  select_row()    │                │
  │                 │                  │─────────────────▶│                │
  │                 │                  │                  │  SELECT ...    │
  │                 │                  │                  │───────────────▶│
  │                 │                  │                  │  bank_account  │
  │                 │                  │                  │◀───────────────│
  │                 │  cart->pos['pos_account'] = bank_account            │
  │                 │  cart->payment_terms['cash_sale'] = 1               │
  │                 │◀─────────────────│                  │                │
  │                 │  continue save   │                  │                │
  │────────────────▶│──────────────────────────────────────────────────────▶│
```

## State Diagram: Module Lifecycle

```
┌───────────┐    install_options()    ┌──────────────┐
│  FA Boot  │───────────────────────▶│  Menu Added  │
│           │                        │  (GL/orders) │
│           │    install_access()    └──────────────┘
│           │───────────────────────▶ Security Registered
└───────────┘

┌───────────┐   db_prewrite()        ┌──────────────┐
│  Invoice  │───────────────────────▶│  Lookup Map  │
│  Submit   │                        └──────┬───────┘
│           │                               │ found?
│           │   redirect pos_account  ◀─────┤
│           │   set cash_sale=1       ◀─────┤ YES
│           │                               │
│           │   return true (no-op)   ◀─────┤ NO
└───────────┘
```
