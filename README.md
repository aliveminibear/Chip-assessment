Interest Account Library


## Requirements

- PHP 8.0 or higher
- Composer

## Installation

### Using Composer

```bash
composer install
```

### Using Docker

```bash
make docker-build
```

### Using Makefile

```bash
make setup
```

### Installing make (optional)

If you don't have `make` installed (common on Windows), you can either use Composer commands directly or install `make` as below.

- Windows (PowerShell):

  - Chocolatey (Admin PowerShell):
  ```powershell
  Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = 'Tls12'; iwr https://community.chocolatey.org/install.ps1 -UseBasicParsing | iex
  choco install make -y
  ```

  - Scoop (non-admin):
  ```powershell
  Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
  iwr get.scoop.sh -UseBasicParsing | iex
  scoop install make
  ```

  - Winget + MSYS2:
  ```powershell
  winget install MSYS2.MSYS2
  ```
  Then open "MSYS2 UCRT64" and run:
  ```bash
  pacman -Sy --noconfirm make
  ```

  After installing, restart your shell and verify:
  ```powershell
  make --version
  ```

- macOS (Homebrew):
```bash
brew install make
```

- Linux (Debian/Ubuntu):
```bash
sudo apt update && sudo apt install -y build-essential
```

Note: On Windows, you can always run tests without `make`:
```powershell
composer test
```

## Interest Calculation

Interest is calculated every 3 days based on the account balance:

- **Formula**: `Balance × (Annual Rate / 365) × 3`
- **Minimum Payout**: 1 penny
- **Accumulation**: Interest below 1 penny is accumulated until the next calculation
- **Automatic Payout**: When accumulated interest reaches 1 penny, it's added to the account balance

### Example

For an account with £1,000 balance and 1.02% annual rate:
- 3-day interest = £1,000 × (1.02% / 365) × 3 = £0.84
- This would be paid out immediately as it's above the 1 penny threshold

```
GET /users/{userId}
```

Response:
```json
{
  "id": "88224979-406e-4e32-9458-55836e4e1f95",
  "income": 499999
}
```
Income is expected in pennies (e.g., 499999 = £4,999.99).

## Testing

### Run Tests

```bash
# Using Composer
composer test

# Using Makefile
make test

# Using Docker
make docker-test
```

### Run Tests with Coverage

```bash
# Using Composer
composer test-coverage

# Using Makefile
make test-coverage
```
### Test Structure

- **Unit Tests**: Test individual components in isolation
- **Integration Tests**: Test complete workflows with mocked dependencies
- **Mocked Stats API**: All tests use mocked Stats API responses


### Docker Development


```bash
# Build Docker image
make docker-build

# Run tests in Docker
make docker-test

# Open shell in Docker container
make docker-shell
```
### Available Make Commands

```bash
make help           # Show available commands
make install        # Install dependencies
make test          # Run tests
make test-coverage # Run tests with coverage
make clean         # Clean generated files
make docker-build  # Build Docker image
make docker-test   # Run tests in Docker
make docker-shell  # Open Docker shell
```

## Error Handling

- **InvalidUserIdException**: Invalid UUID format
- **InvalidAmountException**: Invalid money amounts (negative values, insufficient funds)
- **AccountAlreadyExistsException**: Attempting to create duplicate account
- **AccountNotFoundException**: Operations on non-existent accounts
- **StatsApiException**: Stats API communication errors


### In-Memory Storage
- Uses PHP arrays for data persistence as per requirements
- Easily replaceable with database implementations via repository interfaces

### Value Objects
- Money amounts stored in pennies to avoid floating-point precision issues
- Type-safe UserId with UUID validation
- Immutable value objects following DDD principles

### Interest Calculation
- Precise calculation using integer arithmetic
- Proper handling of sub-penny amounts with accumulation
- Configurable calculation intervals (currently 3 days)

### API Integration
- Robust HTTP client with proper error handling
- Configurable timeouts and base URLs
- Graceful handling of API failures