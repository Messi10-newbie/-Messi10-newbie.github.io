import javafx.geometry.*;
import javafx.scene.*;
import javafx.scene.control.*;
import javafx.scene.effect.DropShadow;
import javafx.scene.layout.*;
import javafx.scene.paint.*;
import javafx.scene.text.*;
import javafx.stage.Stage;

public class LoginScreen {

    public static void show() {
        Stage stage = Main.primaryStage;

        // ── Root ──────────────────────────────────────────────────────────
        VBox root = new VBox(20);
        root.setAlignment(Pos.CENTER);
        root.setPadding(new Insets(50));
        root.setStyle("-fx-background-color: linear-gradient(to bottom right, #1a1a2e, #16213e, #0f3460);");

        // ── Logo / Title ─────────────────────────────────────────────────
        Text logo = new Text("🎬");
        logo.setFont(Font.font(60));

        Text title = new Text("Cinema Ticket System");
        title.setFont(Font.font("Arial", FontWeight.BOLD, 28));
        title.setFill(Color.WHITE);

        Text subtitle = new Text("Login to continue");
        subtitle.setFont(Font.font("Arial", 14));
        subtitle.setFill(Color.web("#a0aec0"));

        // ── Card ─────────────────────────────────────────────────────────
        VBox card = new VBox(16);
        card.setAlignment(Pos.CENTER_LEFT);
        card.setPadding(new Insets(30));
        card.setMaxWidth(360);
        card.setStyle("-fx-background-color: rgba(255,255,255,0.08);" +
                      "-fx-background-radius: 16;" +
                      "-fx-border-color: rgba(255,255,255,0.15);" +
                      "-fx-border-radius: 16;");
        card.setEffect(new DropShadow(20, Color.BLACK));

        // Username
        Label userLbl = styledLabel("Username");
        TextField userField = styledField("Enter your username");

        // Password
        Label passLbl = styledLabel("Password");
        PasswordField passField = new PasswordField();
        passField.setPromptText("Enter your password");
        passField.setStyle(fieldStyle());
        passField.setMaxWidth(Double.MAX_VALUE);

        // Error message
        Label errorLbl = new Label();
        errorLbl.setStyle("-fx-text-fill: #fc8181; -fx-font-size: 13;");
        errorLbl.setVisible(false);

        // Login button
        Button loginBtn = new Button("Login");
        loginBtn.setMaxWidth(Double.MAX_VALUE);
        loginBtn.setStyle("-fx-background-color: #e94560;" +
                          "-fx-text-fill: white; -fx-font-size: 15; -fx-font-weight: bold;" +
                          "-fx-padding: 12; -fx-background-radius: 8; -fx-cursor: hand;");
        loginBtn.setOnMouseEntered(e -> loginBtn.setStyle(loginBtn.getStyle().replace("#e94560", "#c03050")));
        loginBtn.setOnMouseExited(e  -> loginBtn.setStyle(loginBtn.getStyle().replace("#c03050", "#e94560")));

        // Register link
        Hyperlink regLink = new Hyperlink("New customer? Register here");
        regLink.setStyle("-fx-text-fill: #63b3ed; -fx-font-size: 13;");
        regLink.setOnAction(e -> showRegisterDialog());

        // ── Login action ─────────────────────────────────────────────────
        Runnable doLogin = () -> {
            String username = userField.getText().trim();
            String password = passField.getText();
            if (username.isEmpty() || password.isEmpty()) {
                showError(errorLbl, "Please fill in all fields.");
                return;
            }
            String role = UserDatabase.authenticate(username, password);
            if (role == null) {
                showError(errorLbl, "Invalid username or password.");
            } else if (role.equals("admin")) {
                AdminDashboard.show(username);
            } else {
                CustomerMenu.show(username);
            }
        };

        loginBtn.setOnAction(e -> doLogin.run());
        passField.setOnAction(e -> doLogin.run());

        card.getChildren().addAll(userLbl, userField, passLbl, passField,
                                  errorLbl, loginBtn, regLink);

        root.getChildren().addAll(logo, title, subtitle, card);

        Scene scene = new Scene(root, 520, 580);
        stage.setScene(scene);
        stage.centerOnScreen();
        stage.show();
    }

    // ── Register Dialog ───────────────────────────────────────────────────
    private static void showRegisterDialog() {
        Stage dlg = new Stage();
        dlg.setTitle("Register - New Customer");
        dlg.initOwner(Main.primaryStage);

        VBox root = new VBox(14);
        root.setPadding(new Insets(30));
        root.setStyle("-fx-background-color: #1a1a2e;");

        Text title = new Text("Create Account");
        title.setFont(Font.font("Arial", FontWeight.BOLD, 22));
        title.setFill(Color.WHITE);

        TextField userF = styledField("Choose a username");
        PasswordField passF = new PasswordField(); passF.setPromptText("Choose a password"); passF.setStyle(fieldStyle());
        PasswordField confF = new PasswordField(); confF.setPromptText("Confirm password"); confF.setStyle(fieldStyle());

        Label msg = new Label(); msg.setStyle("-fx-text-fill: #68d391; -fx-font-size: 13;");
        Label err = new Label(); err.setStyle("-fx-text-fill: #fc8181; -fx-font-size: 13;");

        Button btn = new Button("Create Account");
        btn.setMaxWidth(Double.MAX_VALUE);
        btn.setStyle("-fx-background-color: #38a169; -fx-text-fill: white;" +
                     "-fx-font-size: 14; -fx-font-weight: bold; -fx-padding: 10; -fx-background-radius: 8; -fx-cursor: hand;");

        btn.setOnAction(e -> {
            String u = userF.getText().trim();
            String p = passF.getText();
            String c = confF.getText();
            err.setText(""); msg.setText("");
            if (u.isEmpty() || p.isEmpty()) { err.setText("All fields are required."); return; }
            if (!p.equals(c))              { err.setText("Passwords do not match."); return; }
            if (u.length() < 3)            { err.setText("Username must be ≥ 3 characters."); return; }
            if (UserDatabase.register(u, p)) {
                msg.setText("✔ Account created! You can now login.");
                userF.clear(); passF.clear(); confF.clear();
            } else {
                err.setText("Username already taken.");
            }
        });

        root.getChildren().addAll(title,
            styledLabel("Username"), userF,
            styledLabel("Password"), passF,
            styledLabel("Confirm Password"), confF,
            err, msg, btn);

        dlg.setScene(new Scene(root, 360, 420));
        dlg.show();
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    private static Label styledLabel(String text) {
        Label l = new Label(text);
        l.setStyle("-fx-text-fill: #e2e8f0; -fx-font-size: 13; -fx-font-weight: bold;");
        return l;
    }

    private static TextField styledField(String prompt) {
        TextField tf = new TextField();
        tf.setPromptText(prompt);
        tf.setStyle(fieldStyle());
        tf.setMaxWidth(Double.MAX_VALUE);
        return tf;
    }

    private static String fieldStyle() {
        return "-fx-background-color: rgba(255,255,255,0.1);" +
               "-fx-text-fill: white; -fx-prompt-text-fill: #718096;" +
               "-fx-border-color: rgba(255,255,255,0.2); -fx-border-radius: 6;" +
               "-fx-background-radius: 6; -fx-padding: 10; -fx-font-size: 14;";
    }

    private static void showError(Label lbl, String msg) {
        lbl.setText("⚠ " + msg);
        lbl.setVisible(true);
    }
}
