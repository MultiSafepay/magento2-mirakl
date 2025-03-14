# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2025-03-14
### Added
- PLGMIRAKL-87: Add support for BNPL refunds
- PLGMIRAKL-73: Improve logging for getSellerMultiSafepayAccountId

## [1.1.1] - 2025-01-21
### Changed
- PLGMIRAKL-88: Allow order confirmation emails on mixed shopping carts, on transaction completed

## [1.1.0] - 2024-11-05
### Added
- PLGMIRAKL-71: Add support for BNPL payment methods
- PLGMIRAKL-56: Add transaction date and number to Mirakl refund request

## [1.0.0] - 2024-02-28
### Added
- PLGMIRAKL-51: Handle partially_refunded status for OrderDebit

### Fixed
- PLGMIRAKL-70: Fix division by 0 when creating order through admin backend

### Changed
- PLGMIRAKL-59: Change $accountId type to string

## [1.0.0-RC6] - 2023-10-19
### Added
- PLGMIRAKL-58: Add more logs to the refund cron process

### Fixed
- PLGMIRAKL-67: Fix ShoppingCart refund

### Changed
- PLGMIRAKL-60: Rename "FundRequest" directory as Request

## [1.0.0-RC5] - 2023-10-11
### Fixed
- PLGMIRAKL-64: Fix an error where a refund chargeback transaction, is being done to the wrong MultiSafepay account

## [1.0.0-RC4] - 2023-10-11
### Added
- PLGMIRAKL-9: Support to process refunds

### Changed
- PLGMIRAKL-62: Change cron tasks to run every three minutes

### Fixed
- PLGMIRAKL-58: Fix the shipping tax of shipping methods that belongs to Mirakl, not being included in the Order Request

## [1.0.0-RC3] - 2023-09-22
### Fixed
- PLGMIRAKL-52: Fix an issue where fund transactions could be executed twice when something went wrong during the process.

## [1.0.0-RC2] - 2023-09-19
### Fixed
- PLGMIRAKL-48: Fix an issue where the Commission Collecting Account ID is not being found when is set at store view level

## [1.0.0-RC1] - 2023-08-02
### Added
- PLGMIRAKL-28: Add the PSP ID and the transaction date in the confirm customer debit request

### Changed
- PLGMIRAKL-35: Rename cron job name
- PLGMIRAKL-30: Refactor cron processes handling all exceptions in a single place and improving logs

## [1.0.0-BETA] - 2023-06-30
### Added
- PLGMIRAKL-2: Added support for payment workflow: Pay on Acceptance
