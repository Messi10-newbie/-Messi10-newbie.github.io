import javax.swing.*;
import java.awt.*;
import java.util.HashMap;

public class Main {

    // Admin credentials
    private static final Admin ADMIN = new Admin("admin", "123");

    public static SeatManager seatManager = new SeatManager();

    public static void main(String[] args) {
        showLoginWindow();
    }

    public static void showLoginWindow() {
        JFrame frame = new JFrame("City Multiplex - Login");
        frame.setSize(400, 300);
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        frame.setLayout(new GridLayout(6, 2, 10, 10));

        JTextField     userField = new JTextField();
        JPasswordField passField = new JPasswordField();
        JButton loginBtn  = new JButton("Login");
        JButton signUpBtn = new JButton("Sign Up");

        frame.add(new JLabel("  City Multiplex", SwingConstants.CENTER));
        frame.add(new JLabel(""));
        frame.add(new JLabel("  Username:"));
        frame.add(userField);
        frame.add(new JLabel("  Password:"));
        frame.add(passField);
        frame.add(new JLabel(""));
        frame.add(loginBtn);
        frame.add(new JLabel(""));
        frame.add(signUpBtn);

        frame.setLocationRelativeTo(null);
        frame.setVisible(true);

        loginBtn.addActionListener(e -> {
            String username = userField.getText().trim();
            String password = new String(passField.getPassword());

            if (username.isEmpty() || password.isEmpty()) {
                JOptionPane.showMessageDialog(frame, "Please fill in all fields!", "Error", JOptionPane.ERROR_MESSAGE);
                return;
            }

            // Check Admin login using Admin object
            if (username.equals(ADMIN.getUsername()) && password.equals(ADMIN.getPassword())) {
                JOptionPane.showMessageDialog(frame, "Welcome, Admin!");
                frame.dispose();
                new AdminDashboard(seatManager).setVisible(true);
                return;
            }

            // Check Customer login
            HashMap<String, String> users = UserDatabase.loadUsers();
            if (users.containsKey(username) && users.get(username).equals(password)) {
                Customer customer = new Customer(username, password);
                JOptionPane.showMessageDialog(frame, "Welcome back, " + customer.getUsername() + "!");
                frame.dispose();
                new CustomerMenu(seatManager, username).setVisible(true);
            } else {
                JOptionPane.showMessageDialog(frame, "Invalid username or password!", "Login Failed", JOptionPane.ERROR_MESSAGE);
            }
        });

        signUpBtn.addActionListener(e -> showSignUpWindow());
    }

    public static void showSignUpWindow() {
        JFrame signupFrame = new JFrame("Customer Registration");
        signupFrame.setSize(350, 220);
        signupFrame.setLayout(new GridLayout(4, 2, 5, 5));

        JTextField     newUser = new JTextField();
        JPasswordField newPass = new JPasswordField();
        JButton regBtn = new JButton("Register");

        signupFrame.add(new JLabel("  New Username:"));
        signupFrame.add(newUser);
        signupFrame.add(new JLabel("  New Password:"));
        signupFrame.add(newPass);
        signupFrame.add(new JLabel(""));
        signupFrame.add(regBtn);

        signupFrame.setLocationRelativeTo(null);
        signupFrame.setVisible(true);

        regBtn.addActionListener(e -> {
            String username = newUser.getText().trim();
            String password = new String(newPass.getPassword());

            if (username.isEmpty() || password.isEmpty()) {
                JOptionPane.showMessageDialog(signupFrame, "Please fill in all fields!", "Error", JOptionPane.ERROR_MESSAGE);
                return;
            }

            if (username.equals(ADMIN.getUsername())) {
                JOptionPane.showMessageDialog(signupFrame, "That username is not allowed.", "Error", JOptionPane.ERROR_MESSAGE);
                return;
            }

            if (UserDatabase.userExists(username)) {
                JOptionPane.showMessageDialog(signupFrame,
                    "Username '" + username + "' is already taken!", "Registration Failed", JOptionPane.WARNING_MESSAGE);
                return;
            }

            // Save new customer
            Customer newCustomer = new Customer(username, password);
            UserDatabase.saveUser(newCustomer.getUsername(), newCustomer.getPassword());
            JOptionPane.showMessageDialog(signupFrame, "Account created successfully! You can now login.");
            signupFrame.dispose();
        });
    }
}
