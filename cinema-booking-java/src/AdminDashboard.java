import javafx.collections.*;
import javafx.geometry.*;
import javafx.scene.*;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.scene.text.*;
import javafx.stage.Stage;

import java.util.*;

public class AdminDashboard {

    private static String currentAdmin;

    public static void show(String admin) {
        currentAdmin = admin;
        Stage stage = Main.primaryStage;

        // ── Root layout ────────────────────────────────────────────────────
        BorderPane root = new BorderPane();
        root.setStyle("-fx-background-color: #0d1117;");

        // ── Sidebar ────────────────────────────────────────────────────────
        VBox sidebar = buildSidebar(root);
        root.setLeft(sidebar);

        // Default tab
        root.setCenter(buildDashboardTab());

        stage.setScene(new Scene(root, 1000, 660));
        stage.setTitle("🎬 Cinema Admin — " + admin);
        stage.setResizable(true);
        stage.centerOnScreen();
        stage.show();
    }

    // ── Sidebar ────────────────────────────────────────────────────────────
    private static VBox buildSidebar(BorderPane root) {
        VBox sb = new VBox(6);
        sb.setPrefWidth(200);
        sb.setPadding(new Insets(20, 10, 20, 10));
        sb.setStyle("-fx-background-color: #161b22;");

        Text appTitle = new Text("🎬 CineAdmin");
        appTitle.setFont(Font.font("Arial", FontWeight.BOLD, 18));
        appTitle.setFill(Color.WHITE);

        Text who = new Text("Logged in as: " + currentAdmin);
        who.setFont(Font.font(11));
        who.setFill(Color.web("#8b949e"));

        Separator sep = new Separator();
        sep.setStyle("-fx-background-color: #30363d;");

        Button[] btns = {
            sideBtn("📊  Dashboard",   () -> root.setCenter(buildDashboardTab())),
            sideBtn("🎬  Movies",       () -> root.setCenter(buildMoviesTab())),
            sideBtn("🎟  All Bookings", () -> root.setCenter(buildBookingsTab(null))),
            sideBtn("👥  Users",        () -> root.setCenter(buildUsersTab())),
            sideBtn("💰  Revenue",      () -> root.setCenter(buildRevenueTab())),
        };

        Region spacer = new Region();
        VBox.setVgrow(spacer, Priority.ALWAYS);

        Button logoutBtn = sideBtn("🚪  Logout", () -> LoginScreen.show());
        logoutBtn.setStyle(logoutBtn.getStyle().replace("#21262d", "#3d1515"));

        sb.getChildren().add(appTitle);
        sb.getChildren().add(who);
        sb.getChildren().add(sep);
        sb.getChildren().addAll(btns);
        sb.getChildren().addAll(spacer, logoutBtn);
        return sb;
    }

    private static Button sideBtn(String label, Runnable action) {
        Button b = new Button(label);
        b.setMaxWidth(Double.MAX_VALUE);
        b.setAlignment(Pos.CENTER_LEFT);
        b.setStyle("-fx-background-color: #21262d; -fx-text-fill: #c9d1d9;" +
                   "-fx-font-size: 13; -fx-padding: 10 14; -fx-background-radius: 8; -fx-cursor: hand;");
        b.setOnMouseEntered(e -> b.setStyle(b.getStyle().replace("#21262d", "#30363d")));
        b.setOnMouseExited(e  -> b.setStyle(b.getStyle().replace("#30363d", "#21262d")));
        b.setOnAction(e -> action.run());
        return b;
    }

    // ── Dashboard Tab ──────────────────────────────────────────────────────
    private static VBox buildDashboardTab() {
        VBox box = new VBox(24);
        box.setPadding(new Insets(30));

        Text heading = heading("📊 Dashboard Overview");

        long totalBookings = BookingManager.totalBookings();
        double revenue     = BookingManager.totalRevenue();
        int movies         = MovieManager.loadAll().size();
        int users          = UserDatabase.getAllUsers().size();

        HBox stats = new HBox(16,
            statCard("🎟 Total Bookings", String.valueOf(totalBookings), "#e94560"),
            statCard("💵 Revenue",        String.format("$%.2f", revenue), "#38a169"),
            statCard("🎬 Movies",         String.valueOf(movies), "#3182ce"),
            statCard("👥 Users",          String.valueOf(users), "#d69e2e")
        );
        stats.setAlignment(Pos.CENTER_LEFT);

        // Recent bookings mini-table
        Text recTitle = sectionTitle("Recent Bookings");
        TableView<Booking> table = buildBookingTable(BookingManager.loadAll()
            .stream().limit(10).collect(java.util.stream.Collectors.toList()));
        VBox.setVgrow(table, Priority.ALWAYS);

        box.getChildren().addAll(heading, stats, recTitle, table);
        return box;
    }

    private static VBox statCard(String label, String value, String color) {
        VBox card = new VBox(6);
        card.setPadding(new Insets(18));
        card.setPrefWidth(180);
        card.setStyle("-fx-background-color: #161b22; -fx-background-radius: 12;" +
                      "-fx-border-color: " + color + "55; -fx-border-radius: 12;");
        Text v = new Text(value); v.setFont(Font.font("Arial", FontWeight.BOLD, 28)); v.setFill(Color.web(color));
        Text l = new Text(label); l.setFont(Font.font(12)); l.setFill(Color.web("#8b949e"));
        card.getChildren().addAll(v, l);
        return card;
    }

    // ── Movies Tab ─────────────────────────────────────────────────────────
    private static BorderPane buildMoviesTab() {
        BorderPane bp = new BorderPane();
        bp.setStyle("-fx-background-color: #0d1117;");
        bp.setPadding(new Insets(30));

        VBox top = new VBox(12);
        top.getChildren().add(heading("🎬 Manage Movies"));

        Button addBtn = accentBtn("＋ Add New Movie");
        addBtn.setOnAction(e -> {
            MovieEditor.showAddDialog(null, () -> bp.setCenter(buildMovieList(bp)));
        });
        top.getChildren().add(addBtn);
        bp.setTop(top);
        bp.setCenter(buildMovieList(bp));
        return bp;
    }

    private static ScrollPane buildMovieList(BorderPane parent) {
        VBox list = new VBox(10);
        list.setPadding(new Insets(14, 0, 0, 0));

        for (Movie m : MovieManager.loadAll()) {
            HBox row = new HBox(14);
            row.setAlignment(Pos.CENTER_LEFT);
            row.setPadding(new Insets(14));
            row.setStyle("-fx-background-color: #161b22; -fx-background-radius: 10;");

            VBox info = new VBox(4);
            Text t = new Text(m.getTitle()); t.setFont(Font.font("Arial", FontWeight.BOLD, 15)); t.setFill(Color.WHITE);
            Text d = new Text(m.getGenre() + " | " + m.getDuration() + " | " + m.getRating() + " | $" + m.getBasePrice());
            d.setFont(Font.font(12)); d.setFill(Color.web("#8b949e"));
            info.getChildren().addAll(t, d);

            Region sp = new Region(); HBox.setHgrow(sp, Priority.ALWAYS);

            int avail = SeatManager.availableCount(m.getTitle());
            Label seats = new Label(avail + " seats left");
            seats.setStyle("-fx-background-color: #1f6feb33; -fx-text-fill: #58a6ff;" +
                           "-fx-padding: 4 10; -fx-background-radius: 20; -fx-font-size: 12;");

            Button editBtn   = smallBtn("✏ Edit");
            Button deleteBtn = smallBtn("🗑 Delete");
            deleteBtn.setStyle(deleteBtn.getStyle().replace("#21262d", "#3d1515"));

            editBtn.setOnAction(e -> MovieEditor.showEditDialog(m, () -> parent.setCenter(buildMovieList(parent))));
            deleteBtn.setOnAction(e -> {
                Alert a = confirm("Delete '" + m.getTitle() + "'?");
                a.showAndWait().ifPresent(r -> {
                    if (r == ButtonType.OK) { MovieManager.deleteMovie(m.getTitle()); parent.setCenter(buildMovieList(parent)); }
                });
            });

            row.getChildren().addAll(info, sp, seats, editBtn, deleteBtn);
            list.getChildren().add(row);
        }

        ScrollPane sp = new ScrollPane(list);
        sp.setFitToWidth(true);
        sp.setStyle("-fx-background: transparent; -fx-background-color: transparent;");
        return sp;
    }

    // ── All Bookings Tab ───────────────────────────────────────────────────
    static BorderPane buildBookingsTab(String filterMovie) {
        BorderPane bp = new BorderPane();
        bp.setStyle("-fx-background-color: #0d1117;");
        bp.setPadding(new Insets(30));
        bp.setTop(heading("🎟 All Bookings" + (filterMovie != null ? " — " + filterMovie : "")));

        List<Booking> bookings = filterMovie == null
            ? BookingManager.loadAll()
            : BookingManager.getBookingsForMovie(filterMovie);

        TableView<Booking> table = buildBookingTable(bookings);
        VBox.setVgrow(table, Priority.ALWAYS);
        bp.setCenter(table);
        return bp;
    }

    private static TableView<Booking> buildBookingTable(List<Booking> data) {
        TableView<Booking> tv = new TableView<>();
        tv.setStyle("-fx-background-color: #161b22; -fx-table-cell-border-color: #30363d;");

        tv.getColumns().addAll(
            col("Booking ID",   "bookingId",   120),
            col("Customer",     "username",    100),
            col("Movie",        "movieTitle",  180),
            col("Seat",         "seatCode",     60),
            col("Type",         "seatType",     80),
            col("Total",        "totalPrice",   80),
            col("Snacks",       "snacks",      120),
            col("Date",         "bookingDate", 130),
            col("Status",       "status",       90)
        );

        tv.setItems(FXCollections.observableArrayList(data));
        tv.setColumnResizePolicy(TableView.CONSTRAINED_RESIZE_POLICY);
        return tv;
    }

    @SuppressWarnings("unchecked")
    private static <T> TableColumn<T, String> col(String title, String prop, int w) {
        TableColumn<T, String> c = new TableColumn<>(title);
        c.setCellValueFactory(new PropertyValueFactory<>(prop));
        c.setPrefWidth(w);
        c.setStyle("-fx-alignment: CENTER-LEFT;");
        return c;
    }

    // ── Users Tab ──────────────────────────────────────────────────────────
    private static VBox buildUsersTab() {
        VBox box = new VBox(14);
        box.setPadding(new Insets(30));
        box.getChildren().add(heading("👥 Manage Users"));

        TableView<String[]> tv = new TableView<>();
        tv.setStyle("-fx-background-color: #161b22;");
        TableColumn<String[], String> nameCol = new TableColumn<>("Username");
        nameCol.setCellValueFactory(d -> new javafx.beans.property.SimpleStringProperty(d.getValue()[0]));
        TableColumn<String[], String> roleCol = new TableColumn<>("Role");
        roleCol.setCellValueFactory(d -> new javafx.beans.property.SimpleStringProperty(d.getValue()[1]));

        TableColumn<String[], Void> actionCol = new TableColumn<>("Action");
        actionCol.setCellFactory(c -> new TableCell<>() {
            final Button btn = new Button("🗑 Delete");
            { btn.setStyle("-fx-background-color: #3d1515; -fx-text-fill: #fc8181;" +
                           "-fx-font-size: 12; -fx-background-radius: 6; -fx-cursor: hand;");
              btn.setOnAction(e -> {
                String user = getTableView().getItems().get(getIndex())[0];
                if (!user.equals(currentAdmin)) {
                    UserDatabase.deleteUser(user);
                    getTableView().getItems().remove(getIndex());
                }
            }); }
            @Override protected void updateItem(Void v, boolean empty) {
                super.updateItem(v, empty); setGraphic(empty ? null : btn);
            }
        });

        tv.getColumns().addAll(nameCol, roleCol, actionCol);
        tv.setItems(FXCollections.observableArrayList(UserDatabase.getAllUsers()));
        tv.setColumnResizePolicy(TableView.CONSTRAINED_RESIZE_POLICY);
        VBox.setVgrow(tv, Priority.ALWAYS);
        box.getChildren().add(tv);
        return box;
    }

    // ── Revenue Tab ────────────────────────────────────────────────────────
    private static VBox buildRevenueTab() {
        VBox box = new VBox(18);
        box.setPadding(new Insets(30));
        box.getChildren().add(heading("💰 Revenue Report"));

        double total = BookingManager.totalRevenue();
        long bookings = BookingManager.totalBookings();

        box.getChildren().add(statCard("💵 Total Revenue",  String.format("$%.2f", total),   "#38a169"));
        box.getChildren().add(statCard("🎟 Total Bookings", String.valueOf(bookings),         "#e94560"));

        box.getChildren().add(sectionTitle("Revenue by Movie"));

        VBox movieRows = new VBox(8);
        Map<String, Long> byMovie = BookingManager.bookingsByMovie();
        byMovie.forEach((movie, count) -> {
            HBox row = new HBox(10);
            row.setPadding(new Insets(10));
            row.setStyle("-fx-background-color: #161b22; -fx-background-radius: 8;");
            Text t = new Text(movie); t.setFill(Color.WHITE); t.setFont(Font.font(13));
            Region sp2 = new Region(); HBox.setHgrow(sp2, Priority.ALWAYS);
            Text c = new Text(count + " bookings"); c.setFill(Color.web("#58a6ff")); c.setFont(Font.font(13));
            row.getChildren().addAll(t, sp2, c);
            movieRows.getChildren().add(row);
        });

        ScrollPane sp = new ScrollPane(movieRows);
        sp.setFitToWidth(true);
        sp.setStyle("-fx-background: transparent; -fx-background-color: transparent;");
        VBox.setVgrow(sp, Priority.ALWAYS);
        box.getChildren().add(sp);
        return box;
    }

    // ── Style helpers ──────────────────────────────────────────────────────
    private static Text heading(String t) {
        Text tx = new Text(t);
        tx.setFont(Font.font("Arial", FontWeight.BOLD, 22));
        tx.setFill(Color.WHITE);
        return tx;
    }

    private static Text sectionTitle(String t) {
        Text tx = new Text(t);
        tx.setFont(Font.font("Arial", FontWeight.BOLD, 16));
        tx.setFill(Color.web("#8b949e"));
        return tx;
    }

    private static Button accentBtn(String label) {
        Button b = new Button(label);
        b.setStyle("-fx-background-color: #e94560; -fx-text-fill: white;" +
                   "-fx-font-size: 13; -fx-padding: 9 18; -fx-background-radius: 8; -fx-cursor: hand;");
        return b;
    }

    private static Button smallBtn(String label) {
        Button b = new Button(label);
        b.setStyle("-fx-background-color: #21262d; -fx-text-fill: #c9d1d9;" +
                   "-fx-font-size: 12; -fx-padding: 5 10; -fx-background-radius: 6; -fx-cursor: hand;");
        return b;
    }

    private static Alert confirm(String msg) {
        Alert a = new Alert(Alert.AlertType.CONFIRMATION, msg, ButtonType.OK, ButtonType.CANCEL);
        a.setHeaderText(null);
        return a;
    }
}
