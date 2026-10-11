Works. Running at **http://localhost:8000**.

Fixed 1 bug found during run:
- [Auth/admin_login.php:14](Auth/login.php#L14) included `connection.php` (doesn't exist in `Auth/`) → 500 on admin login. Changed to `../php/connection.php`.

Made distributor demo login usable: set all 6 demo distributor passwords to `user@123` in `demo.sqlite`.

Verified all 3 roles:
| Role | Login | Result |
|------|-------|--------|
| Admin | `admin` / `admin` | 302 → dashboard ✅ |
| Customer | card `181553046405072` / `user@123` | ✅ |
| Distributor | PDS `1201` / `user@123` | ✅ |

Node API (`/api/node.php`) responds, all pages return correct codes (200 public / 302 auth-gated).

README: expanded Quick Start — demo-login table, MySQL path, no-`.bat` run command, ESP32 steps.

Server up now. Next time: double-click `start-demo.bat`.